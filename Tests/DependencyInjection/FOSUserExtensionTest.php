<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Tests\DependencyInjection;

use FOS\UserBundle\DependencyInjection\FOSUserExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class FOSUserExtensionTest extends TestCase
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
    public function testUserLoadThrowsExceptionUnlessDatabaseDriverSet()
    {
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        unset($config['db_driver']);
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUserLoadThrowsExceptionUnlessDatabaseDriverIsValid()
    {
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        $config['db_driver'] = 'foo';
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUserLoadThrowsExceptionUnlessFirewallNameSet()
    {
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        unset($config['firewall_name']);
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUserLoadThrowsExceptionUnlessUserModelClassSet()
    {
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        unset($config['user_class']);
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testCustomDriverWithoutManager()
    {
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        $config['db_driver'] = 'custom';
        $loader->load(array($config), new ContainerBuilder());
    }

    public function testCustomDriver()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        $config['db_driver'] = 'custom';
        $config['service']['user_manager'] = 'acme.user_manager';
        $loader->load(array($config), $this->configuration);

        $this->assertNotHasDefinition('fos_user.user_manager.default');
        $this->assertAlias('acme.user_manager', 'fos_user.user_manager');
        $this->assertParameter('custom', 'fos_user.storage');
    }

    public function testDisableRegistration()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        $config['registration'] = false;
        $loader->load(array($config), $this->configuration);
        $this->assertNotHasDefinition('fos_user.registration.form.factory');

        $mailer = $this->configuration->getDefinition('fos_user.mailer.default');
        $parameters = $this->configuration->getParameterBag()->resolveValue(
            $mailer->getArgument(3)
        );
        $this->assertSame(
            array(
                'confirmation' => array('no-registration@acme.com' => 'Acme Ltd'),
                'resetting' => array('admin@acme.org' => 'Acme Corp'),
            ),
            $parameters['from_email']
        );
    }

    public function testDisableResetting()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        $config['resetting'] = false;
        $loader->load(array($config), $this->configuration);
        $this->assertNotHasDefinition('fos_user.resetting.form.factory');

        $mailer = $this->configuration->getDefinition('fos_user.mailer.default');
        $parameters = $this->configuration->getParameterBag()->resolveValue(
            $mailer->getArgument(3)
        );
        $this->assertSame(
            array(
                'confirmation' => array('admin@acme.org' => 'Acme Corp'),
                'resetting' => array('no-resetting@acme.com' => 'Acme Ltd'),
            ),
            $parameters['from_email']
        );
    }

    /**
     * @dataProvider providerEmailsDisabledFeature
     */
    public function testEmailsDisabledFeature($testConfig, $registration, $resetting)
    {
        $this->configuration = new ContainerBuilder();
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        $config = array_merge($config, $testConfig);
        $loader->load(array($config), $this->configuration);

        $this->assertParameter($registration, 'fos_user.registration.confirmation.from_email');
        $this->assertParameter($resetting, 'fos_user.resetting.email.from_email');
    }

    public function providerEmailsDisabledFeature()
    {
        $configBothFeaturesDisabled = array('registration' => false, 'resetting' => false);
        $configResettingDisabled = array('resetting' => false);
        $configRegistrationDisabled = array('registration' => false);
        $configOverridenRegistrationEmail = array(
            'registration' => array(
                'confirmation' => array(
                    'from_email' => array('address' => 'ltd@acme.com', 'sender_name' => 'Acme Ltd'),
                ),
            ),
        );
        $configOverridenResettingEmail = array(
            'resetting' => array(
                'email' => array(
                    'from_email' => array('address' => 'ltd@acme.com', 'sender_name' => 'Acme Ltd'),
                ),
            ),
        );

        $default = array('admin@acme.org' => 'Acme Corp');
        $overriden = array('ltd@acme.com' => 'Acme Ltd');

        return array(
            array($configBothFeaturesDisabled, array('no-registration@acme.com' => 'Acme Ltd'), array('no-resetting@acme.com' => 'Acme Ltd')),
            array($configResettingDisabled, $default, array('no-resetting@acme.com' => 'Acme Ltd')),
            array($configRegistrationDisabled, array('no-registration@acme.com' => 'Acme Ltd'), $default),
            array($configOverridenRegistrationEmail, $overriden, $default),
            array($configOverridenResettingEmail, $default, $overriden),
        );
    }

    public function testUserLoadModelClassWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter('Acme\MyBundle\Document\User', 'fos_user.model.user.class');
    }

    public function testUserLoadModelClass()
    {
        $this->createFullConfiguration();

        $this->assertParameter('Acme\MyBundle\Entity\User', 'fos_user.model.user.class');
    }

    public function testUserLoadManagerClassWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter('orm', 'fos_user.storage');
        $this->assertParameter(null, 'fos_user.model_manager_name');
        $this->assertAlias('fos_user.user_manager.default', 'fos_user.user_manager');
    }

    public function testUserLoadManagerClass()
    {
        $this->createFullConfiguration();

        $this->assertParameter('orm', 'fos_user.storage');
        $this->assertParameter('custom', 'fos_user.model_manager_name');
        $this->assertAlias('acme_my.user_manager', 'fos_user.user_manager');
    }

    public function testUserLoadFormClass()
    {
        $this->createFullConfiguration();

        $this->assertParameter('acme_my_registration', 'fos_user.registration.form.type');
        $this->assertParameter('acme_my_resetting', 'fos_user.resetting.form.type');
    }

    public function testUserLoadFormNameWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter('fos_user_registration_form', 'fos_user.registration.form.name');
        $this->assertParameter('fos_user_resetting_form', 'fos_user.resetting.form.name');
    }

    public function testUserLoadFormName()
    {
        $this->createFullConfiguration();

        $this->assertParameter('acme_registration_form', 'fos_user.registration.form.name');
        $this->assertParameter('acme_resetting_form', 'fos_user.resetting.form.name');
    }

    public function testUserLoadFormServiceWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertHasDefinition('fos_user.registration.form.factory');
        $this->assertHasDefinition('fos_user.resetting.form.factory');
    }

    public function testUserLoadFormService()
    {
        $this->createFullConfiguration();

        $this->assertHasDefinition('fos_user.registration.form.factory');
        $this->assertHasDefinition('fos_user.resetting.form.factory');
    }

    public function testUserLoadConfirmationEmailWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter(false, 'fos_user.registration.confirmation.enabled');
        $this->assertParameter(array('admin@acme.org' => 'Acme Corp'), 'fos_user.registration.confirmation.from_email');
        $this->assertParameter('@FOSUser/Registration/email.txt.twig', 'fos_user.registration.confirmation.template');
        $this->assertParameter('@FOSUser/Resetting/email.txt.twig', 'fos_user.resetting.email.template');
        $this->assertParameter(array('admin@acme.org' => 'Acme Corp'), 'fos_user.resetting.email.from_email');
        $this->assertParameter(86400, 'fos_user.resetting.token_ttl');
    }

    public function testUserLoadConfirmationEmail()
    {
        $this->createFullConfiguration();

        $this->assertParameter(true, 'fos_user.registration.confirmation.enabled');
        $this->assertParameter(array('register@acme.org' => 'Acme Corp'), 'fos_user.registration.confirmation.from_email');
        $this->assertParameter('AcmeMyBundle:Registration:mail.txt.twig', 'fos_user.registration.confirmation.template');
        $this->assertParameter('AcmeMyBundle:Resetting:mail.txt.twig', 'fos_user.resetting.email.template');
        $this->assertParameter(array('reset@acme.org' => 'Acme Corp'), 'fos_user.resetting.email.from_email');
        $this->assertParameter(7200, 'fos_user.resetting.retry_ttl');
    }

    public function testUserLoadUtilServiceWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertAlias('fos_user.mailer.default', 'fos_user.mailer');
    }

    public function testUserLoadUtilService()
    {
        $this->createFullConfiguration();

        $this->assertAlias('acme_my.mailer', 'fos_user.mailer');
    }

    /**
     * @dataProvider userManagerSetFactoryProvider
     *
     * @param $dbDriver
     * @param $doctrineService
     */
    public function testUserManagerSetFactory($dbDriver, $doctrineService)
    {
        $this->configuration = new ContainerBuilder();
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        $config['db_driver'] = $dbDriver;
        $loader->load(array($config), $this->configuration);

        $definition = $this->configuration->getDefinition('fos_user.object_manager');

        $this->assertAlias($doctrineService, 'fos_user.doctrine_registry');

        if (method_exists($definition, 'getFactory')) {
            $factory = $definition->getFactory();

            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factory[0]);
            $this->assertSame('fos_user.doctrine_registry', (string) $factory[0]);
            $this->assertSame('getManager', $factory[1]);
        } else {
            $this->assertSame('fos_user.doctrine_registry', $definition->getFactoryService());
            $this->assertSame('getManager', $definition->getFactoryMethod());
        }
    }

    /**
     * @return array
     */
    public function userManagerSetFactoryProvider()
    {
        return array(
            array('orm', 'doctrine'),
        );
    }

    protected function createEmptyConfiguration()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new FOSUserExtension();
        $config = $this->getEmptyConfig();
        $loader->load(array($config), $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    protected function createFullConfiguration()
    {
        $this->configuration = new ContainerBuilder();
        $loader = new FOSUserExtension();
        $config = $this->getFullConfig();
        $loader->load(array($config), $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
     * getEmptyConfig.
     *
     * @return array
     */
    protected function getEmptyConfig()
    {
        $yaml = <<<EOF
db_driver: orm
firewall_name: fos_user
user_class: Acme\MyBundle\Document\User
from_email:
    address: admin@acme.org
    sender_name: Acme Corp
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @return mixed
     */
    protected function getFullConfig()
    {
        $yaml = <<<EOF
db_driver: orm
firewall_name: fos_user
use_listener: true
user_class: Acme\MyBundle\Entity\User
model_manager_name: custom
from_email:
    address: admin@acme.org
    sender_name: Acme Corp
registration:
    confirmation:
        from_email:
            address: register@acme.org
            sender_name: Acme Corp
        enabled: true
        template: AcmeMyBundle:Registration:mail.txt.twig
    form:
        type: acme_my_registration
        name: acme_registration_form
        validation_groups: [acme_registration]
resetting:
    retry_ttl: 7200
    token_ttl: 86400
    email:
        from_email:
            address: reset@acme.org
            sender_name: Acme Corp
        template: AcmeMyBundle:Resetting:mail.txt.twig
    form:
        type: acme_my_resetting
        name: acme_resetting_form
        validation_groups: [acme_resetting]
service:
    mailer: acme_my.mailer
    user_manager: acme_my.user_manager
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param string $value
     * @param string $key
     */
    private function assertAlias($value, $key)
    {
        $this->assertSame($value, (string) $this->configuration->getAlias($key), sprintf('%s alias is correct', $key));
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

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
    }
}
