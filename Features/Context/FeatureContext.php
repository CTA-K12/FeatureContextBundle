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
        $element = $this->getSession()->getPage()->find('css', 'div.select2-result-label:contains(' . $item . ')');
        if (is_null($element)) {
            throw new \Exception('Could not find ' . $item . ' in the select2 dropdown list');
        }
        $element->click();
    }

    /**
     * @When /^I click on select2Multi item "([^"]*)"$/
     */
    public function IClickOnSelectMultiItem($item)
    {
        $element = $this->getSession()->getPage()->find('css', 'div.select2-result-label:contains(' . $item . ')');
        if (is_null($element)) {
            throw new \Exception('Could not find ' . $item . ' in the select2 dropdown list');
        }
        $element->click();
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
        $this->getSession()->wait(5000, "$('span.filtered').text() != $('.total').text()");
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
        $timestamp = $this->getSession()->getPage()->find('css', '#calendar-info-time')->getHtml();
        $today->setTimestamp(intval($timestamp));
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
        $check = clone $today;
        $check->modify('+1 month');
        if (intval($today->format('m') + 1 < intval($check->format('m')))) {
            $today->modify('last day of next month');
        }
        else {
            $today->modify('+1 month');
        }
        $nextMonth = $today->format('F Y'); //This returns a string with full name of month and 4 digit year
        $date = $this->getSession()->getPage()->find('css', '.calendar-date');
        if ($date->getText() != $nextMonth) {
            throw new \Exception(
                'The month for the calendar ' . $date->getText() . ' is not the next month: ' . $nextMonth->format('F Y')
            );
        }
    }

    /**
     * @Then /^I should see last month$/
     */
    public function IShouldSeeLastMonth()
    {
        $today = new \DateTime();
        $check = clone $today;
        $check->modify('-1 month');
        if (intval($today->format('m') - 1 < intval($check->format('m')))) {
            $today->modify('last day of last month');
        }
        else {
            $today->modify('-1 month');
        }
        $lastMonth = $today->format('F Y'); //This returns a string with full name of month and 4 digit year
        $date = $this->getSession()->getPage()->find('css', '.calendar-date');
        if ($date->getText() != $lastMonth) {
            throw new \Exception(
                'The month for the calendar ' . $date->getText() . ' is not the last month: ' . $lastMonth->format('F Y')
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

    /**
     * @When /^I should see icon "(.+?)"$/
     */

    public function IShouldSeeIcon($icon)
    {
        $element = $this->getSession()->getPage()->find('css', '.'.$icon);
        if (!$element) {
            throw new \Exception(
                $icon.' not present contrary to expectation'
            );
        }
    }

    /**
     * @When /^I should not see icon "(.+?)"$/
     */

    public function IShouldNotSeeIcon($icon)
    {
        $element = $this->getSession()->getPage()->find('css', '.'.$icon);
        if ($element) {
            throw new \Exception(
                $icon.' present contrary to expectation'
            );
        }
    }

    /**
     * @Given /^I click on id "([^"]*)"$/
     */
    public function iClickId($arg1)
    {
        $element = $this->getSession()->getPage()->find('css', '#' . $arg1);
        if (is_null($element)) {
            throw new \Exception('Could not find the element with id ' . $arg1);
        }
        try {
            $element->click();
        } catch (\Exception $e) {
            throw new \Exception('Found element with id ' . $arg1 . ' but could not click on it');
        }
    }

    /**
     * @Given /^I select2 search for "([^"]*)"$/
     */
    public function iSelect2SearchFor($arg1)
    {
        $element = $this->getSession()->getPage()->find('css', '.select2-drop-active > .select2-search > .select2-input');
        if (is_null($element)) {
            throw new \Exception('Could not find the active select2 input');
        }
        $element->setValue($arg1);
    }

    /**
     * @Given /^I resize to tablet$/
     */
    public function iResizeToTablet()
    {
        $this->getSession()->getDriver()->resizeWindow(920, 1200,'current');
    }

    /**
     * @Given /^I resize to full$/
     */
    public function iResizeToFull()
    {
        $this->getSession()->getDriver()->resizeWindow(1600, 900,'current');
    }

    /**
     * @Given /^I resize to mobile$/
     */
    public function iResizeToMobile()
    {
        $this->getSession()->getDriver()->resizeWindow(480, 600,'current');
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
