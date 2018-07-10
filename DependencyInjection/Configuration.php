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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $rootNode = $builder->root('msalsas_voting');
        $rootNode->children()
            ->scalarNode('user_provider')
                ->isRequired()
                    ->defaultValue('\App\Entity\User')
            ->end()
            ->arrayNode('negative_reasons')
                ->isRequired()
                    ->scalarPrototype()
                        ->defaultValue([
                            'msalsas_voting.negative_reasons.irrelevant',
                            'msalsas_voting.negative_reasons.old',
                            'msalsas_voting.negative_reasons.tiredness',
                            'msalsas_voting.negative_reasons.sensationalist',
                            'msalsas_voting.negative_reasons.spam',
                            'msalsas_voting.negative_reasons.duplicated',
                            'msalsas_voting.negative_reasons.microblogging',
                            'msalsas_voting.negative_reasons.erroneous',
                            'msalsas_voting.negative_reasons.plagiarism',
                        ])
                ->end()
            ->end()
            ->integerNode('anonymous_percent_allowed')
                ->isRequired()
                    ->defaultValue(2)
                    ->min(1)
            ->end()
            ->integerNode('anonymous_min_allowed')
                ->isRequired()
                    ->defaultValue(50)
                    ->min(1)
            ->end()
            ->end();

        return $builder;
    }
}
