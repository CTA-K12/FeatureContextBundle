<?php

namespace MESD\Behat\MinkBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext extends MinkContext
                  implements KernelAwareInterface
{
    private $kernel;
    private $parameters;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^I do nothing$/
     */
    public function iDoNothing()
    {

    }

    /**
     * @When /^I click on select2 "([^"]*)"$/
     */
    public function iClickOnSelect2($field)
    {
        $this->getSession()->getPage()->find('css', 'div#' . $field . ' > a')->click();
    }

    /**
     * @When /^I wait for select2 to populate$/
     */
    public function iWaitForSelect2ToPopulate()
    {
        $this->getSession()->wait(5000, "$('.select2-searching').length < 1");
    }

    /**
     * @When /^I click on select2 item "([^"]*)"$/
     */
    public function iClickOnSelectItem($item)
    {
        $this->getSession()->getPage()->find('css', 'div.select2-result-label:contains(' . $item . ')')->click();
    }

    /**
     * @When /^I wait (\d+) ms$/
     */
    public function iWait2ms($ms)
    {
        $this->getSession()->wait($ms);
    }

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument)
//    {
//        $container = $this->kernel->getContainer();
//        $container->get('some_service')->doSomethingWith($argument);
//    }
//
}
