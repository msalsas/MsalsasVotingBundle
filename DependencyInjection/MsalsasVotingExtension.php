<?php

/*
 * This file is part of the MsalsasVotingBundle package.
 *
 * (c) Manolo Salsas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Msalsas\VotingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class MsalsasVotingExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.xml');
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('msalsas_voting.negative_reasons', $config['negative_reasons']);
        $container->setParameter('msalsas_voting.anonymous_percent_allowed', $config['anonymous_percent_allowed']);
        $container->setParameter('msalsas_voting.anonymous_min_allowed', $config['anonymous_min_allowed']);
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $doctrineConfig = [];
        $doctrineConfig['orm']['resolve_target_entities']['Msalsas\VotingBundle\Entity\Vote\UserInterface'] = $config['user_provider'];
        $doctrineConfig['orm']['mappings'][] = array(
            'name' => 'MsalsasVotingBundle',
            'is_bundle' => true,
            'type' => 'xml',
            'prefix' => 'Msalsas\VotingBundle\Entity'
        );
        $container->prependExtensionConfig('doctrine', $doctrineConfig);

        $twigConfig = [];
        $twigConfig['globals']['msalsas_voting_voter'] = "@msalsas_voting.voter";
        $twigConfig['globals']['msalsas_voting_clicker'] = "@msalsas_voting.clicker";
        $twigConfig['paths'][__DIR__ . '/../Resources/views'] = "msalsas_voting";
        $twigConfig['paths'][__DIR__ . '/../Resources/public'] = "msalsas_voting.public";
        $container->prependExtensionConfig('twig', $twigConfig);
    }

    public function getAlias()
    {
        return 'msalsas_voting';
    }
}