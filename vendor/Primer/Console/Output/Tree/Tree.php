<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 12/21/14
 * Time: 11:42 AM
 */

/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    Ryan Sullivan <rsullivan@connectstudios.com>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace Primer\Console\Output\Tree;

/**
 * The `Tree` class is used to display data in a tree-like format.
 */
class Tree
{
    protected $_renderer;
    protected $_data = array();

    public function __construct()
    {
        $this->_renderer = new Markdown();
    }

    /**
     * Sets the renderer used by this tree.
     *
     * @param Renderer $renderer The renderer to use for output.
     *
     * @see   tree\Renderer
     * @see   tree\Ascii
     * @see   tree\Markdown
     */
    public function setRenderer(Renderer $renderer)
    {
        $this->_renderer = $renderer;
    }

    /**
     * Set the data.
     * Format:
     *     [
     *         'Label' => [
     *             'Thing' => ['Thing'],
     *         ],
     *         'Thing',
     *     ]
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Render the tree and return it as a string.
     *
     * @return string|null
     */
    public function render()
    {
        return $this->_renderer->render($this->_data);
    }

    /**
     * Display the rendered tree
     */
    public function display()
    {
        echo $this->render();
    }
}
