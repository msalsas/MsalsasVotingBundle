<?php

/*
 * This file is part of the MsalsasVotingBundle package.
 *
 * (c) Manolo Salsas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Msalsas\VotingBundle\Tests\DependencyInjection;

use Msalsas\VotingBundle\DependencyInjection\MsalsasVotingExtension;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class MsalsasVotingExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    protected $configuration;

    protected function tearDown()
    {
        $this->configuration = null;
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUserLoadThrowsExceptionUnlessUserProviderSet()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new MsalsasVotingExtension();
        $config = $this->getEmptyConfig();
        unset($config['user_provider']);
        $loader->load(array($config), $this->configuration);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUserLoadThrowsExceptionUnlessUserProviderIsValid()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new MsalsasVotingExtension();
        $config = $this->getEmptyConfig();
        $config['user_provider'] = 5;
        $loader->load(array($config), $this->configuration);
    }

    public function testUserProvider()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new MsalsasVotingExtension();
        $config = $this->getFullConfig();
        $config['user_provider'] = '\App\Entity\User';
        $loader->load(array($config), $this->configuration);

        $this->assertHasDefinition('msalsas_voting.voter');
        $this->assertHasDefinition('msalsas_voting.clicker');
        $this->assertParameter(50, 'msalsas_voting.anonymous_percent_allowed');
        $this->assertParameter(2, 'msalsas_voting.anonymous_min_allowed');
    }

    public function testPrependConfig()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new MsalsasVotingExtension();
        $config = $this->getFullConfig();
        $config['user_provider'] = '\App\Entity\User';

        $this->configuration->prependExtensionConfig('msalsas_voting', $config);
        $loader->prepend($this->configuration);

        // Doctrine config
        $doctrineConfig = $this->configuration->getExtensionConfig('doctrine');
        $this->assertTrue(isset($doctrineConfig) && is_array($doctrineConfig));
        $this->assertTrue(isset($doctrineConfig[0]['orm']['resolve_target_entities']['Msalsas\VotingBundle\Entity\Vote\UserInterface']));
        $this->assertTrue($doctrineConfig[0]['orm']['resolve_target_entities']['Msalsas\VotingBundle\Entity\Vote\UserInterface'] === '\App\Entity\User');
        $this->assertTrue(isset($doctrineConfig[0]['orm']['mappings']));
        $mapping = array(
            'name' => 'MsalsasVotingBundle',
            'is_bundle' => true,
            'type' => 'xml',
            'prefix' => 'Msalsas\VotingBundle\Entity'
        );
        $this->assertTrue(in_array($mapping, $doctrineConfig[0]['orm']['mappings']));

        // Twig config
        $twigConfig = $this->configuration->getExtensionConfig('twig');
        $this->assertTrue(isset($twigConfig) && is_array($twigConfig));
        $this->assertTrue(isset($twigConfig[0]['globals']['msalsas_voting_voter']));
        $this->assertTrue($twigConfig[0]['globals']['msalsas_voting_voter'] === "@msalsas_voting.voter");
        $this->assertTrue(isset($twigConfig[0]['globals']['msalsas_voting_clicker']));
        $this->assertTrue($twigConfig[0]['globals']['msalsas_voting_clicker'] === "@msalsas_voting.clicker");

        $mainDir = substr(__DIR__, 0, strpos(__DIR__, '/Tests/DependencyInjection') );
        $this->assertTrue(isset($twigConfig[0]['paths'][$mainDir . '/DependencyInjection/../Resources/views']));
        $this->assertTrue($twigConfig[0]['paths'][$mainDir . '/DependencyInjection/../Resources/views'] === "msalsas_voting");
        $this->assertTrue(isset($twigConfig[0]['paths'][$mainDir . '/DependencyInjection/../Resources/public']));
        $this->assertTrue($twigConfig[0]['paths'][$mainDir . '/DependencyInjection/../Resources/public'] === "msalsas_voting.public");
    }

    /**
     * getEmptyConfig.
     *
     * @return array
     */
    protected function getEmptyConfig()
    {
        $yaml = <<<EOF
user_provider: null
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * getFullConfig.
     *
     * @return array
     */
    protected function getFullConfig()
    {
        $yaml = <<<EOF
user_provider: Acme\MyBundle\Document\User
negative_reasons:
    - msalsas_voting.negative_reasons.irrelevant
    - msalsas_voting.negative_reasons.old
anonymous_percent_allowed: 50
anonymous_min_allowed: 2
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param mixed  $value
     * @param string $key
     */
    private function assertParameter($value, $key)
    {
        $this->assertSame($value, $this->configuration->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    /**
     * @param string $id
     */
    private function assertHasDefinition($id)
    {
        $this->assertTrue(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
    }
}