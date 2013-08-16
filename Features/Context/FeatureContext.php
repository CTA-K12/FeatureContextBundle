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
    public function IDoNothing()
    {

    }

    /**
     * @When /^I click on select2 "([^"]*)"$/
     */
    public function IClickOnSelect2($field)
    {
        $this->getSession()->getPage()->find('css', 'div#' . $field . ' > a')->click();
    }

    /**
     * @When /^I click on select2Multi "([^"]*)"$/
     */
    public function IClickOnSelect2Multi($field)
    {
        $this->getSession()->getPage()->find('css', 'div#' . $field . ' > ul > li > input' )->click();
    }


    /**
     * @When /^I wait for select2 to populate$/
     */
    public function IWaitForSelect2ToPopulate()
    {
        $this->getSession()->wait(5000, "$('.select2-searching').length < 1");
    }

    /**
     * @When /^I click on select2 item "([^"]*)"$/
     */
    public function IClickOnSelectItem($item)
    {
        $this->getSession()->getPage()->find('css', 'div.select2-result-label:contains(' . $item . ')')->click();
    }

    /**
     * @When /^I click on select2Multi item "([^"]*)"$/
     */
    public function IClickOnSelectMultiItem($item)
    {
        $this->getSession()->getPage()->find('css', 'div.select2-result-label:contains(' . $item . ')')->click();
    }

    /**
     * @When /^I deselect2Multi "([^"]*)"$/
     */
    public function IDeselect2Multi($field)
    {
        $link=$this->getSession()->getPage()->find(
            'xpath',
            $this->getSession()->getSelectorsHandler()->selectorToXpath('css', 'li:contains(' . $field. ') a')
        );

        $link->click();
    }

    /**
     * @When /^I wait (\d+) ms$/
     */
    public function IWait($ms)
    {
        $this->getSession()->wait($ms);
    }

    /**
     * @When /^I grid search for "(.+?)"$/
     */
    public function gridSearch($field)
    {
        $this->getSession()->getPage()->fillField(
            $this->getSession()->getPage()->find('css', 'input.grid-filter-input-query-from')->getAttribute('id')
            ,$field);
        $this->getSession()->getPage()->find('css', 'input.grid-filter-input-query-from')->keyup(' ');

    }

        /**
     * @When /^I wait for grid search to finish$/
     */
    public function IWaitForGridSearchToFinish()
    {
        $this->getSession()->wait(5000, "$('span.filtered').text() != $('span.total').text()");
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
