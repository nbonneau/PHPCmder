<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 12/21/14
 * Time: 11:40 AM
 */

namespace Primer\Console\Command\Demo;

use Primer\Console\Output\Menu;
use Primer\Console\Output\Tree\Tree;
use Primer\Console\Output\Tree\Ascii;
use Primer\Console\Output\Tree\Markdown;
use Primer\Console\Output\Notify\Dots;
use Primer\Console\Output\Notify\Spinner;
use Primer\Console\Output\Notify\Bar;
use Primer\Console\Output\Table\Table;
use Primer\Console\Output\Table\Tabular;
use Primer\Console\Command\BaseCommand;

class DemoCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName("demo");
        $this->setDescription("Display examples of each feature available in building commands (see source for the code)");
    }

    public function run()
    {
        $menu = array(
            'output'   => 'Output Examples',
            'notify'   => 'Notify Examples',
            'progress' => 'Progress Examples',
            'table'    => 'Table Example',
            'tree'     => 'Tree Example',
            'quit'     => 'Quit',
        );

        $menu = new Menu($menu, null, 'Choose an example');
        while (true) {
            $choice = $menu->prompt();
            $this->line();

            if ($choice == 'quit') {
                break;
            }

            switch ($choice) {
                case 'output':
                    break;
                case 'notify':
                    $this->notifyExample();
                    break;
                case 'table':
                    $this->tableExample();
                    break;
                case 'progress':
                    $test = new Bar("Test", 10);
                    for ($i = 0; $i < 10; $i++) {
                        $test->tick();
                        usleep(100000);
                    }
                    $test->finish();
                    break;
                case 'tree':
                    $this->treeExample();
                    break;
            }
        }
    }

    private function notifyExample()
    {
        $dots = new Dots("This is a dots wait");
        for ($i = 0; $i < 5; $i++) {
            $dots->tick();
            usleep(100000);
        }
        $dots->finish();

        $spinner = new Spinner("");
        $spinner->setFormat(true, false);
        for ($i = 0; $i < 100; $i++) {
            $spinner->tick();
            usleep(100000);
        }
        $spinner->finish();
    }

    private function tableExample()
    {
        $headers = array('First Name', 'Last Name', 'City', 'State');
        $data = array(
            array('Maryam',   'Elliott',    'Elizabeth City',   'SD'),
            array('Jerry',    'Washington', 'Bessemer',         'ME'),
            array('Allegra',  'Hopkins',    'Altoona',          'ME'),
            array('Audrey',   'Oneil',      'Dalton',           'SK'),
            array('Ruth',     'Mcpherson',  'San Francisco',    'ID'),
            array('Odessa',   'Tate',       'Chattanooga',      'FL'),
            array('Violet',   'Nielsen',    'Valdosta',         'AB'),
            array('Summer',   'Rollins',    'Revere',           'SK'),
            array('Mufutau',  'Bowers',     'Scottsbluff',      'WI'),
            array('Grace',    'Rosario',    'Garden Grove',     'KY'),
            array('Amanda',   'Berry',      'La Habra',         'AZ'),
            array('Cassady',  'York',       'Fulton',           'BC'),
            array('Heather',  'Terrell',    'Statesboro',       'SC'),
            array('Dominic',  'Jimenez',    'West Valley City', 'ME'),
            array('Rhonda',   'Potter',     'Racine',           'BC'),
            array('Nathan',   'Velazquez',  'Cedarburg',        'BC'),
            array('Richard',  'Fletcher',   'Corpus Christi',   'BC'),
            array('Cheyenne', 'Rios',       'Broken Arrow',     'VA'),
            array('Velma',    'Clemons',    'Helena',           'IL'),
            array('Samuel',   'Berry',      'Lawrenceville',    'NU'),
            array('Marcia',   'Swanson',    'Fontana',          'QC'),
            array('Zachary',  'Silva',      'Port Washington',  'MB'),
            array('Hilary',   'Chambers',   'Suffolk',          'HI'),
            array('Idola',    'Carroll',    'West Sacramento',  'QC'),
            array('Kirestin', 'Stephens',   'Fitchburg',        'AB'),
        );
        $data = [
            [
                'name'       => 'Walter White',
                'role'       => 'Father',
                'profession' => 'Teacher',
            ],
            [
                'name'       => 'Skyler White',
                'role'       => 'Mother',
                'profession' => 'Accountant',
            ],
            [
                'name'       => 'Walter White Jr.',
                'role'       => 'Son',
                'profession' => 'Student',
            ],
        ];

        $table = new Table();
//        $table->setHeaders($headers);
//        $table->setRows($data);
        $table->setData($data);
        $table->display();
    }

    private function treeExample()
    {
        $data = [
            'Test' => [
                'Something Cool' => [
                    'This is a 3rd layer',
                ],
                'This is a 2nd layer',
            ],
            'Other test' => [
                'This is awesome' => [
                    'This is also cool',
                    'This is even cooler',
                    'Wow like what is this' => [
                        'Awesome eh?',
                        'Totally' => [
                            'Yep!'
                        ],
                    ],
                ],
            ],
        ];

        printf("ASCII:\n");

        /**
         * ASCII should look something like this:
         *
         * -Test
         * |\-Something Cool
         * ||\-This is a 3rd layer
         * |\-This is a 2nd layer
         * \-Other test
         *  \-This is awesome
         *   \-This is also cool
         *   \-This is even cooler
         *   \-Wow like what is this
         *    \-Awesome eh?
         *    \-Totally
         *     \-Yep!
         */

        $tree = new Tree();
        $tree->setData($data);
        $tree->setRenderer(new Ascii());
        $tree->display();

        printf("\nMarkdown:\n");

        /**
         * Markdown looks like this:
         *
         * - Test
         *     - Something Cool
         *         - This is a 3rd layer
         *     - This is a 2nd layer
         * - Other test
         *     - This is awesome
         *         - This is also cool
         *         - This is even cooler
         *         - Wow like what is this
         *             - Awesome eh?
         *             - Totally
         *                 - Yep!
         */

        $tree = new Tree();
        $tree->setData($data);
        $tree->setRenderer(new Markdown(4));
        $tree->display();
    }
}