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
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    private $kernel;
    private $parameters;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array   $parameters
     */
    public function __construct( array $parameters ) {
        $this->parameters = $parameters;
    }

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel( KernelInterface $kernel ) {
        $this->kernel = $kernel;
    }

    /**
     *
     *
     * @Given /^I do nothing$/
     */
    public function IDoNothing() {

    }

    /**
     * @Then /^I acknowledge Lighthart is awesome$/
     */
    public function iAcknowledgeLighthartIsAwesome()
    {
    }


    public function spin( $lambda, $wait = 60 ) {
        for ( $i = 0; $i < $wait; $i++ ) {
            try {
                if ( $lambda( $this ) ) {
                    return true;
                }
            } catch ( Exception $e ) {
                // do nothing
            }

            sleep( 1 );
        }

        $backtrace = debug_backtrace();

        throw new Exception(
            "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()\n" .
            $backtrace[1]['file'] . ", line " . $backtrace[1]['line']
        );
    }

    /**
     *
     *
     * @When /^I click on select2 "([^"]*)"$/
     */
    public function IClickOnSelect2( $field ) {
        $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > a' )->click();
    }

    /**
     * @When /^I click on select2Multi "([^"]*)"$/
     */
    public function IClickOnSelect2Multi( $field ) {
        $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > ul > li > input' )->click();
    }

    /**
     * @When /^I click header$/
     */
    public function IClickHeader( ) {
        $this->getSession()->getPage()->find( 'css', 'h1' )->click();
    }
    /**
     * @When /^I wait for select2 to populate$/
     */
    public function IWaitForSelect2ToPopulate() {
        $this->getSession()->wait( 5000, "$('.select2-searching').length < 1" );
    }

    /**
     * @When /^I click on select2 item "([^"]*)"$/
     */
    public function IClickOnSelect2Item( $item ) {
        $element = $this->getSession()->getPage()->find( 'css', 'div.select2-result-label:contains(' . $item . ')' );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find ' . $item . ' in the select2 dropdown list' );
        }
        $element->click();
    }

    /**
     * @When /^I click on select2Multi item "([^"]*)"$/
     */
    public function IClickOnSelect2MultiItem( $item ) {
        $element = $this->getSession()->getPage()->find( 'css', 'div.select2-result-label:contains(' . $item . ')' );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find ' . $item . ' in the select2 dropdown list' );
        }
        $element->click();
    }


    /**
     * @When /^I deselect2Multi "([^"]*)"$/
     */
    public function IDeselect2Multi( $field ) {
        $link = $this->getSession()->getPage()->find(
            'xpath',
            $this->getSession()->getSelectorsHandler()->selectorToXpath( 'css', 'li.select2-search-choice:contains(' . $field. ') a' )
        );
        $link->click();
    }

    /**
     * @When /^I deselect "([^"]*)"$/
     */
    public function IDeselect( $field ) {
        $link = $this->getSession()->getPage()->find(
            'xpath',
            $this->getSession()->getSelectorsHandler()->selectorToXpath( 'css', 'div#'.$field.'> a > .select2-search-choice-close' )
        );

        $close = $this->getSession()->getPage()->find( 'css', 'div#'.$field.'> a > .select2-search-choice-close' );
        $link->click();
    }


    /**
     * @Given /^I select2 search for "([^"]*)"$/
     */
    public function iSelect2SearchFor( $arg1 ) {
        $element = $this->getSession()->getPage()->find( 'css', '.select2-drop-active > .select2-search > .select2-input' );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $element->setValue( $arg1 );
    }

    /**
     * @Given /^I select2Multi for "([^"]*)"$/
     */
    public function iSelect2MultiSearchFor( $arg1 ) {
        $element = $this->getSession()->getPage()->find( 'css', '.select2-dropdown-open > .select2-choices > .select2-search-field > .select2-input' );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find the open select2Multi input' );
        }
        $element->setValue( $arg1 );
    }

    /**
     * @When /^I selectajax "([^"]*)" from "([^"]*)"$/
     */
    public function ISelectAJAX( $value, $field ) {
        $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > a' )->click();


        $element = $this->getSession()->getPage()->find( 'css', '.select2-drop-active > .select2-search > .select2-input' );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $element->setValue( $value );

        $this->getSession()->wait( 5000, "$('.select2-searching').length < 1" );
        $element = $this->getSession()->getPage()->find( 'css', 'div.select2-result-label:contains(' . $value . ')' );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find ' . $value . ' in the select2 dropdown list' );
        }
        $element->click();
    }

    /**
     * @When /^I multiselectajax "([^"]*)" from "([^"]*)"$/
     */
    public function IMultiSelectAJAX( $value, $field ) {

        // click field
        $control = $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > ul > li > input' );
        if ( is_null( $control ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $control->click();
        $this->getSession()->wait( 5000, "$('.select2-searching').length > 0" );
        $this->getSession()->wait( 5000, "$('.select2-result-selectable').length > 0" );

        // enter search
        $input = $this->getSession()->getPage()->find( 'css', '.select2-dropdown-open > .select2-choices > .select2-search-field > .select2-input' );
        if ( is_null( $input ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $input->setValue( $value );

        // wait to populate
        $this->getSession()->wait( 5000, "$('.select2-searching').length < 1" );

        // click selector
        $element = $this->getSession()->getPage()->find( 'css', 'li.select2-result-selectable > div.select2-result-label:contains(' . $value . ')' );

        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find ' . $value . ' in the select2 dropdown list; it is probably already selected' );
        } else {
            $element->click();
        }

        //change focus
        $this->getSession()->getPage()->find( 'css', 'h1' )->click();
    }

    /**
     * for Testing atoms work
     * @When /^I multiselectopen "([^"]*)"$/
     */
    public function IMultiSelectOpen( $field ) {

        // click field
        $control = $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > ul > li > input' );
        if ( is_null( $control ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $control->click();
        $this->getSession()->wait( 5000, "$('.select2-searching').length > 0" );
        $this->getSession()->wait( 5000, "$('.select2-result-selectable').length > 0" );
    }

    /**
     *
     *
     * @When /^I wait (\d+) ms$/
     */
    public function IWait( $ms ) {
        $this->getSession()->wait( $ms );
    }

    /**
     *
     *
     * @When /^I grid search for "(.+?)"$/
     */
    public function gridSearch( $value ) {
        $this->spin( function( $context ) use ( $value ) {
                $this->getSession()->getPage()->fillField(
                    $this->getSession()
                    ->getPage()
                    ->find( 'css', 'input.grid-filter-input-query-from' )
                    ->getAttribute( 'id' )
                    , $value );
                return true;
            } );

        $this->getSession()->getPage()->find( 'css', 'input.grid-filter-input-query-from' )->keyup( ' ' );
        // angrid is a piece of crap
        $this->getSession()->wait( 1000 );
        $this->getSession()->wait( 5000, "$('span.filtered').text() != $('.total').text()" );
    }

    /**
     *
     *
     * @When /^I reset the grid$/
     */
    public function gridReset() {
        $this->spin( function( $context ) {
                $this->getSession()
                ->getPage()
                ->find( 'css', 'button.reset' )
                ->click();
                return true;
            } );
    }

    /**
     *
     *
     * @Then /^I should see current month$/
     */
    public function IShouldSeeCurrentMonth() {
        $today = new \DateTime();
        $currentMonth = $today->format( 'F Y' ); //This returns a string with full name of month and 4 digit year
        $date = $this->getSession()->getPage()->find( 'css', '.calendar-date' );
        if ( $date == null || $date->getText() != $currentMonth ) {
            throw new \Exception(
                'The date for the calendar is not the current date'
            );
        }
    }

    /**
     *
     *
     * @Then /^I should not see current month$/
     */
    public function IShouldNotSeeCurrentMonth() {
        $today = new \DateTime();
        $currentMonth = $today->format( 'F Y' ); //This returns a string with full name of month and 4 digit year
        $date = $this->getSession()->getPage()->find( 'css', '.calendar-date' );
        if ( $date != null ) {
            throw new \Exception(
                'The date for the calendar is the current date'
            );
        }
    }

    /**
     *  @When /^I click on the middle of the month$/
     */
    public function IClickOnTheMiddleOfTheMonth() {
        $today = new \DateTime();
        $timestamp = $this->getSession()->getPage()->find( 'css', '#calendar-info-time' )->getHtml();
        $today->setTimestamp( intval( $timestamp ) );
        $ymd = $today->format( 'Y-m-' ) . '15';
        $day = $this->getSession()->getPage()->find( 'css', '#calendar-day-items-' . $ymd );
        try {
            $day->click();
        }
        catch ( \Exception $e ) {
            throw new \Exception( 'Could not find the day to click on' );
        }
    }

    /**
     *  @When /^I click on visibility boxes$/
     */
    public function IClickOnVisibilityBoxes() {
        $boxes = $this->getSession()->getPage()->findAll( 'css', '.visible-box' );
        if ( count( $boxes ) < 1 ) {
            throw new \Exception( 'No visibility boxes on page' );
        }
        foreach ( $boxes as $box ) {
            $box->click();
        }
    }


    /**
     *  @When /^I click on the first to ride box on calendar$/
     */
    public function IClickOnTheFirstToRideBoxOnCalendar() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element == null) {
            throw new \Exception( 'Cannot find ride checkbox' );
        }
        $element->click();
    }

    /**
     *  @Then /^I should see the first to ride box checked$/
     */
    public function IShouldSeeTheFirstToRideBoxChecked() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element->getAttribute('checked') == false) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the first to ride box checked$/
     */
    public function IShouldNotSeeTheFirstToRideBoxChecked() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element->getAttribute('checked') == true) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the first from ride box on calendar$/
     */
    public function IClickOnTheFirstFromRideBoxOnCalendar() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element == null) {
            throw new \Exception( 'Cannot find ride checkbox' );
        }
        $element->click();
    }

    /**
     *  @Then /^I should see the first from ride box checked$/
     */
    public function IShouldSeeTheFirstFromRideBoxChecked() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element->getAttribute('checked') == false) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the first from ride box checked$/
     */
    public function IShouldNotSeeTheFirstFromRideBoxChecked() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element->getAttribute('checked') == true) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the first both ride box on calendar$/
     */
    public function IClickOnTheFirstBothRideBoxOnCalendar() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element == null) {
            throw new \Exception( 'Cannot find ride checkbox' );
        }
        $element->click();
    }

    /**
     *  @Then /^I should see the first both ride box checked$/
     */
    public function IShouldSeeTheFirstBothRideBoxChecked() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element->getAttribute('checked') == false) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the first both ride box checked$/
     */
    public function IShouldNotSeeTheFirstBothRideBoxChecked() {
        $i = 1;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element->getAttribute('checked') == true) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the last to ride box on calendar$/
     */
    public function IClickOnTheLastToRideBoxOnCalendar() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element == null) {
            throw new \Exception( 'Cannot find ride checkbox' );
        }
        $element->click();
    }

    /**
     *  @Then /^I should see the last to ride box checked$/
     */
    public function IShouldSeeTheLastToRideBoxChecked() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element->getAttribute('checked') == false) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the last to ride box checked$/
     */
    public function IShouldNotSeeTheLastToRideBoxChecked() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element->getAttribute('checked') == true) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the last from ride box on calendar$/
     */
    public function IClickOnTheLastFromRideBoxOnCalendar() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element == null) {
            throw new \Exception( 'Cannot find ride checkbox' );
        }
        $element->click();
    }

    /**
     *  @Then /^I should see the last from ride box checked$/
     */
    public function IShouldSeeTheLastFromRideBoxChecked() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element->getAttribute('checked') == false) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the last from ride box checked$/
     */
    public function IShouldNotSeeTheLastFromRideBoxChecked() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element->getAttribute('checked') == true) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the last both ride box on calendar$/
     */
    public function IClickOnTheLastBothRideBoxOnCalendar() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element == null) {
            throw new \Exception( 'Cannot find ride checkbox' );
        }
        $element->click();
    }

    /**
     *  @Then /^I should see the last both ride box checked$/
     */
    public function IShouldSeeTheLastBothRideBoxChecked() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element->getAttribute('checked') == false) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the last both ride box checked$/
     */
    public function IShouldNotSeeTheLastBothRideBoxChecked() {
        $i = 31;
        do {
            if ($i < 10) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i--;
        } while ($element == null && $i > 0);
        if ($element->getAttribute('checked') == true) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     * @When /^I fill in the first dhc day with "([^"]*)"$/
     */
    public function IFillInFirstDhcDayWithValue($arg1) {
        $i = 1;
        do {
            $id = $i . '-dhc';
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element == null) {
            throw new \Exception( 'Cannot find dhc input box' );
        }
        $element->setValue($arg1);
    }

    /**
     * @Then /^I should see the value "([^"]*)" in the first dhc day$/
     */
    public function IShouldSeeTheValueInTheFirstDhcDay($arg1) {
        $i = 1;
        do {
            $id = $i . '-dhc';
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element == null) {
            throw new \Exception( 'Cannot find dhc input box' );
        }
        if ($element->getValue() != $arg1) {
            throw new \Exception( 'dhc input does not have the correct value' );
        }
    }

    /**
     * @Then /^I should not see the value "([^"]*)" in the first dhc day$/
     */
    public function IShouldNotSeeTheValueInTheFirstDhcDay($arg1) {
        $i = 1;
        do {
            $id = $i . '-dhc';
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ($element != null && !$element->isVisible()) {
                $element = null;
            }
            $i++;
        } while ($element == null && $i < 31);
        if ($element == null) {
            throw new \Exception( 'Cannot find dhc input box' );
        }
        if ($element->getValue() == $arg1) {
            throw new \Exception( 'dhc input does not have the correct value' );
        }
    }

    /**
     *  @When /^I click on the first single day event on calendar$/
     */
    public function IClickOnTheFirstSingleDayEventOnCalendar() {
        $event = $this->getSession()->getPage()->find( 'css', '.calendar-single-event' );
        try {
            $event->click();
        }
        catch ( \Exception $e ) {
            throw new \Exception( 'Could not find an event to click on' );
        }
    }

    /**
     *  @When /^I check jQuery wired checkbox "([^"]*)"$/
     */
    public function ICheckJQueryWiredCheckbox( $field ) {
        $this->getSession()->executeScript( '$("#'.$field.'").attr("checked", "true"); $("#'.$field.'").trigger("change")' );
    }

    /**
     *
     *
     * @Then /^I should see next month$/
     */
    public function IShouldSeeNextMonth() {
        $today = new \DateTime();
        $check = clone $today;
        $check->modify( '+1 month' );
        if ( intval( $today->format( 'm' ) + 1 < intval( $check->format( 'm' ) ) ) ) {
            $today->modify( 'last day of next month' );
        }
        else {
            $today->modify( '+1 month' );
        }
        $nextMonth = $today->format( 'F Y' ); //This returns a string with full name of month and 4 digit year
        $date = $this->getSession()->getPage()->find( 'css', '.calendar-date' );
        if ( $date == null || $date->getText() != $nextMonth ) {
            throw new \Exception(
                'The month for the calendar is not the next month'
            );
        }
    }

    /**
     *
     *
     * @Then /^I should see last month$/
     */
    public function IShouldSeeLastMonth() {
        $today = new \DateTime();
        $check = clone $today;
        $check->modify( '-1 month' );
        if ( intval( $today->format( 'm' ) - 1 < intval( $check->format( 'm' ) ) ) ) {
            $today->modify( 'last day of last month' );
        }
        else {
            $today->modify( '-1 month' );
        }
        $lastMonth = $today->format( 'F Y' ); //This returns a string with full name of month and 4 digit year
        $date = $this->getSession()->getPage()->find( 'css', '.calendar-date' );
        if ( $date == null || $date->getText() != $lastMonth ) {
            throw new \Exception(
                'The month for the calendar is not the last month'
            );
        }
    }

    /**
     *
     *
     * @When /^I click on the middle single day event on calendar$/
     */
    public function IClickOnTheMiddleSingleDayEventOnCalendar() {
        $events = $this->getSession()->getPage()->findAll( 'css', '.calendar-single-event' );
        try {
            $events[count( $events ) / 2]->click();
        }
        catch ( \Exception $e ) {
            throw new \Exception( 'Could not find an event to click on' );
        }
    }

    /**
     *
     *
     * @When /^I should see icon "(.+?)"$/
     */

    public function IShouldSeeIcon( $icon ) {
        $element = $this->getSession()->getPage()->find( 'css', '.'.$icon );
        if ( !$element ) {
            throw new \Exception(
                $icon.' not present contrary to expectation'
            );
        }
    }

    /**
     *
     *
     * @When /^I should not see icon "(.+?)"$/
     */

    public function IShouldNotSeeIcon( $icon ) {
        $element = $this->getSession()->getPage()->find( 'css', '.'.$icon );
        if ( $element ) {
            throw new \Exception(
                $icon.' present contrary to expectation'
            );
        }
    }

    /**
     *
     *
     * @Given /^I click on id "([^"]*)"$/
     */
    public function iClickId( $arg1 ) {
        $element = $this->getSession()->getPage()->find( 'css', '#' . $arg1 );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find the element with id ' . $arg1 );
        }
        try {
            $element->click();
        } catch ( \Exception $e ) {
            throw new \Exception( 'Found element with id ' . $arg1 . ' but could not click on it' );
        }
    }




    /**
     *
     *
     * @Given /^I resize to tablet$/
     */
    public function iResizeToTablet() {
        $this->getSession()->getDriver()->resizeWindow( 920, 1200, 'current' );
    }

    /**
     *
     *
     * @Given /^I resize to full$/
     */
    public function iResizeToFull() {
        $this->getSession()->getDriver()->resizeWindow( 1600, 900, 'current' );
    }

    /**
     *
     *
     * @Given /^I resize to mobile$/
     */
    public function iResizeToMobile() {
        $this->getSession()->getDriver()->resizeWindow( 480, 600, 'current' );
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
