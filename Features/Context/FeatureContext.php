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

    /**
     * @Then /^I should see current month$/
     */
    public function IShouldSeeCurrentMonth()
    {
        $today = new \DateTime();
        $currentMonth = $today->format('F Y'); //This returns a string with full name of month and 4 digit year
        $date = $this->getSession()->getPage()->find('css', '.calendar-date');
        if ($date->getText() != $currentMonth) {
            throw new \Exception(
                'The date for the calendar is not the current date'
            );
        }
    }

    /**
     *  @When /^I click on the middle of the month$/
     */
    public function IClickOnTheMiddleOfTheMonth()
    {
        $today = new \DateTime();
        $ymd = $today->format('Y-m-') . '15';
        $day = $this->getSession()->getPage()->find('css', '#calendar-day-items-' . $ymd);
        try {
            $day->click();
        }
        catch (\Exception $e) {
            throw new \Exception('Could not find the day to click on');
        }
    }

    /**
     *  @When /^I click on visibility boxes$/
     */
    public function IClickOnVisibilityBoxes()
    {
        $boxes = $this->getSession()->getPage()->findAll('css', '.visible-box');
        if (count($boxes) < 1) {
            throw new \Exception('No visibility boxes on page');
        }
        foreach ($boxes as $box) {
            $box->click();
        }
    }

    /**
     *  @When /^I click on the first single day event on calendar$/
     */
    public function IClickOnTheFirstSingleDayEventOnCalendar()
    {
        $event = $this->getSession()->getPage()->find('css', '.calendar-single-event');
        try {
            $event->click();
        }
        catch (\Exception $e) {
            throw new \Exception('Could not find an event to click on');
        }
    }

    /**
     *  @When /^I check jQuery wired checkbox "([^"]*)"$/
     */
    public function ICheckJQueryWiredCheckbox($field) 
    {
        $this->getSession()->executeScript('$("#'.$field.'").attr("checked", "true"); $("#'.$field.'").trigger("change")');
    }

    /**
     * @Then /^I should see next month$/
     */
    public function IShouldSeeNextMonth()
    {
        $today = new \DateTime();
        $today->modify('+1 month');
        $currentMonth = $today->format('F Y'); //This returns a string with full name of month and 4 digit year
        $date = $this->getSession()->getPage()->find('css', '.calendar-date');
        if ($date->getText() != $currentMonth) {
            throw new \Exception(
                'The date for the calendar is not the current date'
            );
        }
    }

    /**
     * @When /^I click on the middle single day event on calendar$/
     */
    public function IClickOnTheMiddleSingleDayEventOnCalendar()
    {
        $events = $this->getSession()->getPage()->findAll('css', '.calendar-single-event');
        try {
            $events[count($events) / 2]->click();
        }
        catch (\Exception $e) {
            throw new \Exception('Could not find an event to click on');
        }
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
