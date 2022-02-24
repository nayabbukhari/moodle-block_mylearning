<?php
// This file is part of Moodle Workplace https://moodle.com/workplace based on Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// Moodle Workplace™ Code is the collection of software scripts
// (plugins and modifications, and any derivations thereof) that are
// exclusively owned and licensed by Moodle under the terms of this
// proprietary Moodle Workplace License ("MWL") alongside Moodle's open
// software package offering which itself is freely downloadable at
// "download.moodle.org" and which is provided by Moodle under a single
// GNU General Public License version 3.0, dated 29 June 2007 ("GPL").
// MWL is strictly controlled by Moodle Pty Ltd and its certified
// premium partners. Wherever conflicting terms exist, the terms of the
// MWL are binding and shall prevail.

namespace block_mylearning;

use context_system;
use core_privacy\local\metadata\collection;
use block_mylearning\privacy\provider;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

/**
 * Tests for the privacy provider class methods.
 *
 * @covers      \block_mylearning\privacy\provider
 * @package     block_mylearning
 * @copyright   2022 Moodle Pty Ltd <support@moodle.com>
 * @author      2022 Odei Alba <odei.alba@moodle.com>
 * @license     Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class provider_test extends provider_testcase {

    /**
     * setUp.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test class export_user_preferences method
     *
     * @return void
     */
    public function test_export_user_preferences() {
        $user1 = self::getDataGenerator()->create_user();
        $context = context_system::instance();

        // Export user preferences when user has no preferences.
        provider::export_user_preferences($user1->id);
        $preferences = writer::with_context($context)->get_user_preferences('block_mylearning');

        // Test empty preferences.
        $this->assertObjectNotHasAttribute('block_mylearning_status_filter', $preferences);
        $this->assertObjectNotHasAttribute('block_mylearning_sort_filter', $preferences);
        $this->assertObjectNotHasAttribute('block_mylearning_view_filter', $preferences);

        // Test preferences with data.
        set_user_preference('block_mylearning_status_filter', 'notcompleted', $user1->id);
        set_user_preference('block_mylearning_sort_filter', 'mylearningname', $user1->id);
        set_user_preference('block_mylearning_view_filter', 'viewlist', $user1->id);

        // Export user preferences when user has preferences.
        provider::export_user_preferences($user1->id);
        $preferences = writer::with_context($context)->get_user_preferences('block_mylearning');

        // Test preferences with data.
        $this->assertEquals('notcompleted', $preferences->block_mylearning_status_filter->value);
        $this->assertEquals('mylearningname', $preferences->block_mylearning_sort_filter->value);
        $this->assertEquals('viewlist', $preferences->block_mylearning_view_filter->value);
    }

    /**
     * Test provider::get_metadata
     */
    public function test_get_metadata() {
        $collection = new collection('block_mylearning');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(3, $itemcollection);

        $filteroptions = ['status', 'sort', 'view'];
        foreach ($filteroptions as $index => $filteroption) {
            $item = $itemcollection[$index];
            $name = 'block_mylearning_' . $filteroption . '_filter';
            $this->assertEquals($name, $item->get_name());
            $this->assertEquals('privacy:metadata:preference:' . $name, $item->get_summary());
        }
    }
}
