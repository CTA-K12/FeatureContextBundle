<?php

namespace MESD\Behat\MinkBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

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

    private $logspin = 0;

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


    // Waits for itm to be true.
    // Tries condition every 1 ms for 5000 cycles
    // catches exceptions until time limit, then fails

    public function spin( $lambda, $wait = 5000 ) {
        for ( $i = 0; $i < $wait; $i++ ) {
            if ( $this->logspin > 1 && $i > 0 ) {
                print_r( 'Spin iteration:'.$i."\n" );
            }
            try {
                if ( $lambda( $this ) ) {
                    if ( $this->logspin && $i > 0 ) {
                        print_r( 'Spin: '.$i.' ms '."\n" );
                    }
                    return true;
                }
            } catch ( \Exception $e ) {
                // do nothing
            }
            usleep( 1000 );
        }

        $backtrace = debug_backtrace();

        throw new \Exception(
            "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()\n" .
            $backtrace[1]['file'] . ", line " . $backtrace[1]['line']
        );
    }


    /**
     *
     *
     * @Given /^I do nothing$/
     */
    public function IDoNothing() {
    }

    /**
     * Because it is true
     *
     * @Then /^I acknowledge Lighthart is awesome$/
     */
    public function IAcknowledgeLighthartIsAwesome() {
    }

    /**
     * Clicks link with specified id|title|alt|text.
     *
     * @When /^(?:|I )goto "(?P<link>(?:[^"]|\\")*)"$/
     */
    public function IClickLink( $link ) {
        parent::clickLink( $link );

        if ( 'Edit' == $link ) {
            $this->waitForAllSelects( 0 );
        }
    }


    /**
     * Just to defocus
     *
     * @When /^I click header$/
     */
    public function IClickHeader( ) {
        $this->getSession()->getPage()->find( 'css', 'h1' )->click();
    }

    /**
     *  This is used for select2 forms associated with Calendaers
     *
     *  @When /^I wait for button to unpush$/
     */
    public function IButtonToUnpush() {
        $this->spin( function( $context ) {
                return !$this->getSession()->getPage()->find( 'css', '.button-pushed' );
            }
        );
    }

    /**
     * Ajax loading can be slow.  Wait for it here.
     *
     * @When /^I wait for all selects$/
     */

    public function waitForAllSelects() {

        $this->waitForSingleSelects();
        $this->waitForMultipleSelects();
    }


    /**
     * Ajax loading can be slow.  Wait for it here.
     *
     * @When I wait for single selects$/
     */

    public function waitForSingleSelects() {
        $this->spin( function( $context ) {
                $selected = $this->getSession()->getPage()->find( 'css', '#footer' );
                return $selected;
            } );
        $i = 0;
        // singles
        $divs = $this->getSession()->getPage()->findAll( 'css', 'div.s2 > a.select2-choice > span' );
        while ( $i < 5000 && $divs != array() ) {
            $divs = array_filter( $divs, function( $e ) {
                    if ( $e ) {
                        // print_r( $e->getHTML()."\n" );
                    }
                    return !$e->getHTML();
                } );

            $i++;
            usleep( 1000 );
        }
    }

    /**
     * Ajax loading can be slow.  Wait for it here.
     *
     * @When I wait for multiple selects$/
     */

    public function waitForMultipleSelects() {
        $this->spin( function( $context ) {
                $element = $this->getSession()->getPage()->find( 'css', 'div.select2-loading' );
                return !$element;
            }
        );

        //multis
        $divs = $this->getSession()->getPage()
        ->findAll( 'css',
            'div.select2-container.select2-container-multi.s2 > ul.select2-choices > li.select2-search-choice' );
        $i = 0;
        while ( $i < 5000 && $divs != array() ) {
            $divs = array_filter( $divs, function( $e ) {
                    if ( $e ) {
                        // print_r( $e->getHTML() );
                    }
                    return !$e->getHTML();
                } );

            $i++;
            usleep( 1000 );
        }
    }

    /**
     * Open a select box to see a list of items.
     *
     * @When /^I selectOpen "([^"]*)"$/
     */
    public function ISelectOpen( $field ) {
        $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > a' )->click();

        $this->IWaitForSelect2ToOpen( $field );
        $this->IWaitForSelect2ToPopulate();
    }

    /**
     * for Testing atoms work
     *
     * @When /^I multiSelectOpen "([^"]*)"$/
     */
    public function IMultiSelectOpen( $field ) {

        // click field
        $control = $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > ul > li > input' );
        if ( is_null( $control ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $control->click();
        $this->IWaitForSelect2ToPopulate();
    }


    /**
     * for Testing atoms work
     *
     * @When /^I should see a select2option "([^"]*)"$/
     */
    public function IShouldSeeSelectOption( $value ) {

        // This bit keeps it active until the selector is gone.
        $link = $this->spin( function( $context ) use ( $value ) {
                $link = $this->getSession()->getPage()->find(
                    'css', 'div.select2-result-label:contains("' .  $value . '")'
                );
                return $link;
            } );

        if ( is_null( $link ) ) {
            throw new \Exception( 'Could not find the select2 option: '.$value );
        }
    }

    /**
     * for Testing atoms work
     *
     * @When /^I should not see a select2option "([^"]*)"$/
     */
    public function IShouldNotSeeSelectOption( $value ) {

        // This bit keeps it active until the selector is gone.
        $this->spin( function( $context ) use ( $value ) {
                $link = $this->getSession()->getPage()->find(
                    'css', 'div.select2-result-label:contains("' .  $value . '")'
                );
                return !$link;
            } );

        // if ( is_null( $link ) ) {
        //     throw new \Exception( 'Could not find the select2 option: '.$value );
        // }
    }

    /**
     * for Testing atoms work
     *
     * @When /^I should see a select2option "([^"]*)" chosen from "([^"]*)"$/
     */
    public function IShouldSeeSelectOptionChosen( $value, $field ) {
        $this->spin( function( $context ) use ( $value, $field ) {
                $element = $this->getSession()->getPage()->find( 'css',
                    'div#' .$field. ' > a.select2-choice'.' > span:contains("' .  $value . '")'
                );
                return $element;
            }
        );
    }

    /**
     * for Testing atoms work
     *
     * @When /^I should not see a select2option "([^"]*)" chosen from "([^"]*)"$/
     */
    public function IShouldNotSeeSelectOptionChosen( $value, $field ) {
        $this->spin( function( $context ) use ( $value, $field ) {
                $element = $this->getSession()->getPage()->find( 'css',
                    'div#' .$field. ' > a.select2-choice'.' > span:contains("' .  $value . '")'
                );
                return !$element;
            }
        );
    }

    /**
     * for Testing atoms work
     *
     * @When /^I should see a multiSelect2option "([^"]*)" chosen from "([^"]*)"$/
     */
    public function IShouldSeeMultiSelectOptionChosen( $value, $field ) {
        $this->spin( function( $context ) use ( $value, $field ) {
                $element = $this->getSession()->getPage()->find( 'css',
                    'div#' .$field. ' > ul.select2-choices > li.select2-search-choice'.' > div:contains("' .  $value . '")'
                );
                return $element;
            }
        );
    }

    /**
     * for Testing atoms work
     *
     * @When /^I should not see a multiSelect2option "([^"]*)" chosen from "([^"]*)"$/
     */
    public function IShouldNotSeeMultiSelectOptionChosen( $value, $field ) {
        $this->spin( function( $context ) use ( $value, $field ) {
                $element = $this->getSession()->getPage()->find( 'css',
                    'div#' .$field. ' > ul.select2-choices > li.select2-search-choice'.' > div:contains("' .  $value . '")'
                );
                return !$element;
            }
        );
    }

    /**
     * for Testing atoms work and other users
     *
     * @Given /^I select2 search for "([^"]*)"$/
     */
    public function ISelect2SearchFor( $value ) {
        $search = null;
        $this->spin( function( $context ) use ( &$search ) {
                $search = $this->getSession()->getPage()->find( 'css', '.select2-drop-active > .select2-search > .select2-input' );
                return $search ;
            }
        );

        if ( is_null( $search ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }

        $search ->setValue( $value );
        $this->spin( function( $context ) {
                $searching = $this->getSession()->getPage()
                ->find( 'css', 'li.select2-searching' );
                return !$searching ;
            }
        );

        $link = null;

        $this->spin( function( $context ) use ( &$link, $value ) {

                // ajax and non-ajax lists have slightly different css classes.

                $link = $this->getSession()->getPage()->find( 'css', '.select2-match:contains("'.$value.'")' );
                if ( !$link ) {
                    $link = $this->getSession()->getPage()->find( 'css', '.select2-result-label:contains("'.$value.'")' );
                }
                return $link ;
            }
        );

        $link->click();
    }

    /**
     * for Testing atoms work and other users
     *
     * @Given /^I select2Multi search for "([^"]*)"$/
     */
    public function ISelect2MultiSearchFor( $value ) {

        $search = null;
        $this->spin( function( $context ) use ( &$search ) {
                $search = $this->getSession()->getPage()
                ->find( 'css', '.select2-dropdown-open > .select2-choices > .select2-search-field > .select2-input' );
                return $search ;
            }
        );

        if ( is_null( $search  ) ) {
            throw new \Exception( 'Could not find the open select2Multi input' );
        }

        $search->setValue( $value );

        $this->spin( function( $context ) {
                $searching = $this->getSession()->getPage()
                ->find( 'css', 'li.select2-searching' );
                return !$searching ;
            }
        );
    }

    /**
     * for Testing atoms work
     *
     * @When /^I should see a multiSelect2 search option "([^"]*)"$/
     */
    public function IShouldSeeMultiSelectSearchOption( $value ) {
        $this->spin( function( $context ) use ( $value ) {
                $link = $this->getSession()->getPage()->find( 'css',
                    'div.select2-drop-multi > ul.select2-results > li.select2-result > div.select2-result-label:contains("' .  $value . '")'
                );
                return $link;
            } );
    }



    /**
     * Deprecated
     * features/User.feature uses this.
     * switch to private after rewrites.
     * use these instead:
     * ISelectAJAX, IMultiSelectAJAX
     * ISelectOpen, IMultiSelectOpen
     *
     * @When /^I click on select2Multi "([^"]*)"$/
     */
    public function IClickOnSelect2Multi( $field ) {
        $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > ul > li > input' )->click();
    }


    /**
     * For select2 boxes, to uncheck items-- single selects
     *
     * @When /^I deselect "([^"]*)"$/
     */
    public function IDeselect( $field ) {
        $close = $this->getSession()->getPage()->find( 'css', 'div#'.$field.'> a > .select2-search-choice-close' );
        $close->click();
        $this->IWaitForSelect2ToBeOffscreen( $field );
    }

    /**
     * For select2 boxes, to uncheck items-- multiselects
     *
     * TODO: Add checks for flags to behat features don't need
     * to check css classes
     *
     * @When /^I deselectMulti "([^"]*)" from "([^"]*)"$/
     */
    public function IDeselectMulti( $value, $field ) {
        $link = $this->getSession()->getPage()->find(
            'xpath',
            $this->getSession()->getSelectorsHandler()->selectorToXpath( 'css', 'div#'.$field.'> ul > li.select2-search-choice:contains("' . $value. '") a' )
        );
        $link->click();
        $this->getSession()->getPage()->find( 'css', 'h1' )->click();

        // This bit keeps it active until the selector is gone.
        $this->spin( function( $context ) use ( $field, $value ) {
                $link = $this->getSession()->getPage()->find(
                    'xpath',
                    $this->getSession()->getSelectorsHandler()
                    ->selectorToXpath( 'css',
                        'div#'.$field.'> ul > li.select2-search-choice:contains("' . $value. '") a' )
                );
                return !$link;
            } );
    }


    /**
     * TODO: Add checks for flags to behat features don't need
     * to check css classes
     *
     *
     * @When /^I selectAjax "([^"]*)" from "([^"]*)"$/
     */
    public function ISelectAJAX( $value, $field ) {
        $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > a' )->click();

        $this->spin( function( $context ) use ( $value ) {
                return $this->getSession()->getPage()->find( 'css', '.select2-drop-active > .select2-search > .select2-input' );
            }
        );

        $element = $this->getSession()->getPage()->find( 'css', '.select2-drop-active > .select2-search > .select2-input' );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $element->setValue( $value );

        $this->getSession()->wait( 5000, "$('.select2-searching').length < 1" );
        $element = $this->getSession()->getPage()->find( 'css', 'div.select2-result-label:contains("' . $value . '")' );
        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find ' . $value . ' in the select2 dropdown list' );
        }
        $element->click();

        $this->spin( function( $context ) use ( $value ) {
                return !$this->getSession()->getPage()->find( 'css', 'div.select2-result-label:contains("' . $value . '")' );
            }
        );
        $this->IClickHeader();
    }

    /**
     * TODO: Add checks for flags to behat features don't need
     * to check css classes
     *
     * @When /^I multiSelectAjax "([^"]*)" from "([^"]*)"$/
     */
    public function IMultiSelectAJAX( $value, $field ) {
        $this->spin( function( $context ) use ( $field ) {
                return $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > ul > li > input' );
            }
        );


        // click field
        $control = $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > ul > li > input' );
        if ( is_null( $control ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $control->click();

        $this->IWaitForSelect2ToPopulate();

        // enter search
        $input = $this->getSession()->getPage()->find( 'css', '.select2-dropdown-open > .select2-choices > .select2-search-field > .select2-input' );
        if ( is_null( $input ) ) {
            throw new \Exception( 'Could not find the active select2 input' );
        }
        $input->setValue( $value );

        // wait to populate
        $this->getSession()->wait( 5000, "$('.select2-searching').length < 1" );

        // click selector
        $element = $this->getSession()->getPage()->find( 'css', 'li.select2-result-selectable > div.select2-result-label:contains("' . $value . '")' );

        if ( is_null( $element ) ) {
            throw new \Exception( 'Could not find ' . $value . ' in the select2 dropdown list; it is probably already selected' );
        } else {
            $element->click();
        }

        //change focus
        $this->IClickHeader();
    }

    /**
     * TODO: Add checks for flags to behat features don't need
     * to check css classes
     *
     * @When /^I multiSelectAjax search result "([^"]*)" from "([^"]*)"$/
     */
    public function IMultiSelectSearchResultAJAX( $value, $field ) {
        $link = null;
        $this->spin( function( $context ) use ( $value, &$link ) {
                $link = $this->getSession()->getPage()->find( 'css',
                    'div.select2-drop-multi > ul.select2-results > li.select2-result > div.select2-result-label:contains("' .  $value . '")'
                );
                return $link;
            } );
        $link->click();

        //change focus
        $this->IClickHeader();
        $this->IWaitForMultiSelect2ToBeOffscreen( $field );
    }

    /**
     * Not for external use
     */
    private function IWaitForSelect2ToPopulate() {
        $this->spin( function( $context ) {
                $link = $this->getSession()->getPage()->find(
                    'css', '.select2-searching'
                );
                return !$link;
            } );
    }

    /**
     * Not for external use
     */
    private function IWaitForSelect2ToOpen( $field ) {
        $this->spin( function( $context ) use ( $field ) {
                $element = $this->getSession()->getPage()->find( 'css', 'div.select2-drop-active' );
                return $element;
            }
        );

        $this->spin( function( $context ) use ( $field ) {
                $element = $this->getSession()->getPage()->find( 'css', 'div.select2-drop-active.select2-offscreen' );
                return !$element;
            }
        );

        $this->spin( function( $context ) use ( $field ) {
                $element = $this->getSession()->getPage()->find( 'css', 'div#' . $field . '.select2-dropdown-open' );
                return $element;
            }
        );
    }

    /**
     * Not for external use
     */
    private function IWaitForSelect2ToBeOffscreen( $field ) {
        $this->spin( function( $context ) use ( $field ) {
                $element = $this->getSession()->getPage()->find( 'css', 'div#' . $field . ' > div.select2-offscreen' );
                return $element;
            }
        );
    }

    /**
     * Not for external use
     */
    private function IWaitForMultiSelect2ToBeOffscreen( $field ) {
        $this->spin( function( $context ) use ( $field ) {
                $element = $this->getSession()->getPage()->find( 'css',
                    'div.select2-drop-active.select2-drop-multi'
                );
                return !$element;
            }
        );

        $this->spin( function( $context ) use ( $field ) {
                $element = $this->getSession()->getPage()->find( 'css',
                    'div#' . $field . ' > div.select2-offscreen.select2-container-multi.select2-dropdown-open'
                );
                return !$element;
            }
        );
    }


    /**
     * each form should probably have its own one of these
     * key part is the spin function to wait for form to process
     * which tells us when page is reloaded
     *
     * @When /^I save plan$/
     */
    public function ISavePlan( ) {
        $this->getSession()->getPage()->find( 'css', '#save-plan' )->click();

        $this->spin( function( $context ) {
                $element = $this->getSession()->getPage()->find( 'css', 'div.flash-notice' );
                $element = $this->getSession()->getPage()->find( 'css', 'div.alert-success:contains("Plan saved.")' );
                return $element;
            } );
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
     * For pushing divs with javascript
     *
     * @When /^I push id "(.+?)"$/
     */
    public function IPushId( $id ) {
        $element=null;
        $this->spin( function( $context ) use ( $id, &$element ) {
                $element = $this->getSession()->getPage()->find( 'css', '#'.$id );
                return $element;
            }
        );

        $element->click();
    }


    /**
     * For angrid
     *
     * @When /^I grid search for "(.+?)"$/
     */
    public function IGridSearch( $value ) {
        $this->spin( function( $context ) use ( $value ) {
                $field = $this->getSession()->getPage()->find( 'css',
                    'input.grid-filter-input-query-from'
                );

                return $field;
            } );


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
        $this->getSession()->wait( 2000 );
        $this->getSession()->wait( 5000, "$('span.filtered').text() != $('.total').text()" );
    }

    /**
     * For angrid
     *
     * @When /^I reset the grid$/
     */
    public function IGridReset() {
        $reset = null;
        $this->spin( function( $context ) use ( &$reset ) {
                $reset = $this->getSession()
                ->getPage()
                ->find( 'css', 'button.reset' );
                return $reset;
            } );
        $reset->click();
    }

    /**
     * For angrid
     *
     * @When /^I add to caseload$/
     */
    public function IAddToCaseLoad() {
        $this->spin( function( $context ) use ( &$reset ) {
                $add = $this->getSession()
                ->getPage()
                ->find( 'css', '#grid-add-form' );

                $show = $this->getSession()
                ->getPage()
                ->find( 'css', '#show-grid-add-form' );

                return $show || $add;
            }
        );

        $add = $this->getSession()->getPage()->find( 'css', '#show-grid-add-form' );
        if ( $add ) {
            $add->click();

            $this->spin( function( $context ) use ( &$reset ) {
                    $reset = $this->getSession()
                    ->getPage()
                    ->find( 'css', '#grid-add-form' );
                    return $reset;
                } );
        }
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
                'The date for the calendar is the current date and is not expect to be'
            );
        }
    }

    /**
     *  @When /^I load calendar "(.*?)"$/
     */
    public function ILoadCalendar( $link ) {
        parent::clickLink( $link );
        $this->IWaitForAppointmentCalendar();
    }

    /**
     *  @When /^I wait for appointment calendar$/
     */
    public function IWaitForAppointmentCalendar() {
        $this->spin( function( $context ) {
                return !$this->getSession()->getPage()->find( 'css', '#appointment-calendar.hidden' );
            }
        );

        $this->spin( function( $context ) {
                return $this->getSession()->getPage()->find( 'css', '#calendar-loading.hidden' );
            }
        );
    }

    /**
     *  @When /^I wait for dhc calendar$/
     */
    public function IWaitForDHCCalendar() {
        $this->spin( function( $context ) {
                return !$this->getSession()->getPage()->find( 'css', '.button-pushed' );
            }
        );


        $this->spin( function( $context ) {
                return $this->getSession()->getPage()->find( 'css', '#delegatedhealthcare-calendar' );
            }
        );

        $this->spin( function( $context ) {
                return $this->getSession()->getPage()->find( 'css', 'span.calendar-date-display' );
            }
        );
    }

    /**
     *  @When /^I wait for ride calendar$/
     */
    public function IWaitForRideCalendar() {
        $this->spin( function( $context ) {
                return !$this->getSession()->getPage()->find( 'css', '.button-pushed' );
            }
        );

        $this->spin( function( $context ) {
                return $this->getSession()->getPage()->find( 'css', '#ride-calendar' );
            }
        );

        $this->spin( function( $context ) {
                return $this->getSession()->getPage()->find( 'css', 'span.calendar-date-display' );
            }
        );
    }

    /**
     *  @When /^I save dhc calendar$/
     */
    public function SaveDHCCalendar( ) {
        $this->getSession()->getPage()->find( 'css', '#save-calendar' )->click();

        $this->spin( function( $context ) {
                return $this->getSession()->getPage()->find( 'css', '#calendar-messages > .alert-success' );
            }
        );
    }

    /**
     *  @When /^I save ride calendar$/
     */
    public function ISaveRideCalendar( ) {
        $this->getSession()->getPage()->find( 'css', '#save-calendar' )->click();

        $this->spin( function( $context ) {
                return $this->getSession()->getPage()->find( 'css', '#calendar-messages > .alert-success' );
            }
        );
    }

    /**
     *  @When /^I generate dhc logs$/
     */
    public function IGenerateDHCLogs( ) {
        $this->getSession()->getPage()->find( 'css', '#generate-log' )->click();

        $this->spin( function( $context ) {
                return $this->getSession()->getPage()->find( 'css', '#calendar-messages > .alert-success' );
            }
        );

        $this->spin( function( $context ) {
                return !$this->getSession()->getPage()->find( 'css', '.icon-spinner' );
            }
        );

    }

    /**
     *  @When /^I click on the middle of the month$/
     */
    public function IClickOnTheMiddleOfTheMonth() {
        $this->IWaitForAppointmentCalendar();

        $today = new \DateTime();
        $timestamp = $this->getSession()->getPage()->find( 'css', '#calendar-info-time' )->getHtml();
        $today->setTimestamp( intval( $timestamp ) );
        $ymd = $today->format( 'Y-m-' ) . '15';

        $mid = null;
        $this->spin( function( $context ) use ( $ymd, &$mid ) {
                $mid = $this->getSession()->getPage()->find( 'css', '#calendar-day-items-' . $ymd );
                return $mid;
            } ) ;

        $mid->click();

        $this->waitForAllSelects();

        // $day = $this->getSession()->getPage()->find( 'css', '#calendar-day-items-' . $ymd );
        // try {
        //     $day->click();
        // }
        // catch ( \Exception $e ) {
        //     throw new \Exception( 'Could not find the day to click on' );
        // }
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
            if ( $i < 10 ) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element == null ) {
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
            if ( $i < 10 ) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element->getAttribute( 'checked' ) == false ) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the first to ride box checked$/
     */
    public function IShouldNotSeeTheFirstToRideBoxChecked() {
        $i = 1;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element->getAttribute( 'checked' ) == true ) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the first from ride box on calendar$/
     */
    public function IClickOnTheFirstFromRideBoxOnCalendar() {
        $i = 1;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element == null ) {
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
            if ( $i < 10 ) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element->getAttribute( 'checked' ) == false ) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the first from ride box checked$/
     */
    public function IShouldNotSeeTheFirstFromRideBoxChecked() {
        $i = 1;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element->getAttribute( 'checked' ) == true ) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the first both ride box on calendar$/
     */
    public function IClickOnTheFirstBothRideBoxOnCalendar() {
        $i = 1;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element == null ) {
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
            if ( $i < 10 ) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element->getAttribute( 'checked' ) == false ) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the first both ride box checked$/
     */
    public function IShouldNotSeeTheFirstBothRideBoxChecked() {
        $i = 1;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element->getAttribute( 'checked' ) == true ) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the last to ride box on calendar$/
     */
    public function IClickOnTheLastToRideBoxOnCalendar() {
        $i = 31;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element == null ) {
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
            if ( $i < 10 ) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element->getAttribute( 'checked' ) == false ) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the last to ride box checked$/
     */
    public function IShouldNotSeeTheLastToRideBoxChecked() {
        $i = 31;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-to';
            }
            else {
                $id = $i . '-to';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element->getAttribute( 'checked' ) == true ) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the last from ride box on calendar$/
     */
    public function IClickOnTheLastFromRideBoxOnCalendar() {
        $i = 31;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element == null ) {
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
            if ( $i < 10 ) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element->getAttribute( 'checked' ) == false ) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the last from ride box checked$/
     */
    public function IShouldNotSeeTheLastFromRideBoxChecked() {
        $i = 31;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-from';
            }
            else {
                $id = $i . '-from';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element->getAttribute( 'checked' ) == true ) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *  @When /^I click on the last both ride box on calendar$/
     */
    public function IClickOnTheLastBothRideBoxOnCalendar() {
        $i = 31;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element == null ) {
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
            if ( $i < 10 ) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element->getAttribute( 'checked' ) == false ) {
            throw new \Exception( 'checkbox is not checked' );
        }
    }

    /**
     *  @Then /^I should not see the last both ride box checked$/
     */
    public function IShouldNotSeeTheLastBothRideBoxChecked() {
        $i = 31;
        do {
            if ( $i < 10 ) {
                $id = '0' . $i . '-both';
            }
            else {
                $id = $i . '-both';
            }
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i--;
        } while ( $element == null && $i > 0 );
        if ( $element->getAttribute( 'checked' ) == true ) {
            throw new \Exception( 'checkbox is checked' );
        }
    }

    /**
     *
     *
     * @When /^I fill in the first dhc day with "([^"]*)"$/
     */
    public function IFillInFirstDhcDayWithValue( $arg1 ) {
        $i = 1;
        do {
            $id = $i . '-dhc';
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element == null ) {
            throw new \Exception( 'Cannot find dhc input box' );
        }
        $element->setValue( $arg1 );
    }

    /**
     *
     *
     * @Then /^I should see the value "([^"]*)" in the first dhc day$/
     */
    public function IShouldSeeTheValueInTheFirstDhcDay( $arg1 ) {
        $i = 1;
        do {
            $id = $i . '-dhc';
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element == null ) {
            throw new \Exception( 'Cannot find dhc input box' );
        }
        if ( $element->getValue() != $arg1 ) {
            throw new \Exception( 'dhc input does not have the correct value' );
        }
    }

    /**
     *
     *
     * @Then /^I should not see the value "([^"]*)" in the first dhc day$/
     */
    public function IShouldNotSeeTheValueInTheFirstDhcDay( $arg1 ) {
        $i = 1;
        do {
            $id = $i . '-dhc';
            $element = $this->getSession()->getPage()->find( 'css', '#' . $id );
            if ( $element != null && !$element->isVisible() ) {
                $element = null;
            }
            $i++;
        } while ( $element == null && $i < 31 );
        if ( $element == null ) {
            throw new \Exception( 'Cannot find dhc input box' );
        }
        if ( $element->getValue() == $arg1 ) {
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
    public function IClickId( $arg1 ) {
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
    public function IResizeToTablet() {
        $this->getSession()->getDriver()->resizeWindow( 920, 1200, 'current' );
    }

    /**
     *
     *
     * @Given /^I resize to full$/
     */
    public function IResizeToFull() {
        $this->getSession()->getDriver()->resizeWindow( 1600, 900, 'current' );
    }

    /**
     *
     *
     * @Given /^I resize to mobile$/
     */
    public function IResizeToMobile() {
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
