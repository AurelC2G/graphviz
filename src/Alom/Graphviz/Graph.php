<?php
/*
 * This file is part of Alom Graphviz.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alom\Graphviz;

/**
 * Base graph instruction.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
abstract class Graph extends BaseInstruction
{
    /**
     * Parent node
     *
     * @var InstructionInterface
     */
    protected $parent;

    /**
     * Graph identifier
     *
     * @var string
     */
    protected $id;

    /**
     * Name of the graph
     *
     * @var string
     */
    protected $name;

    /**
     * Instructions list
     *
     * @var array
     */
    protected $instructions = array();

    /**
     * Creates a new edge for the graph.
     *
     * @param array $list List of elements of the edge
     *
     * @param array $attributes Associative array of attributes
     *
     * @param InstructionInterface Parent element
     *
     * @return Edge The created edge
     */
    abstract protected function createEdge($list, array $attributes = array(), $parent = null);

    /**
     * Returns the graph header (digraph G as example).
     *
     * @param string $id Identifier of graph
     *
     * @return string The graph header
     */
    abstract protected function getHeader($id);

    /**
     * Creates a new graph.
     *
     * @param string $id Identifier of the graph
     *
     * @param InstructionInterface $parent Parent element
     */
    public function __construct($id, $parent = null)
    {
        $this->parent = $parent;
        $this->id = $id;
    }

    /**
     * Returns identfier of graph.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Adds a new instruction to graph.
     *
     * @param InstructionInterface $instruction Instruction to add
     *
     * @return Graph Fluid-interface
     */
    public function append($instruction)
    {
        $this->instructions[] = $instruction;

        return $this;
    }

    /**
     * Returns list of instructions.
     *
     * @return array
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Adds an assignment instruction.
     *
     * @param string $name Name of the value to assign
     *
     * @param string $value Value to assign
     *
     * @return Graph Fluid-interface
     */
    public function set($name, $value)
    {
        if (in_array($name, array('graph', 'node', 'edge'))) {
            throw new \InvalidArgumentException(sprintf('Use method attr for setting %s', $name));
        }

        $this->instructions[] = new Assign($name, $value);

        return $this;
    }

    /**
     * Define attributes for node/edge/graph.
     *
     * @param string $name Name of type
     *
     * @param array $attributes Attributes of the type
     */
    public function attr($name, array $attributes)
    {
        $this->instructions[] = new AttributeSet($name, $attributes);

        return $this;
    }

    /**
     * Starts a subgraph.
     *
     * @param string $id Identifier of subgraph
     *
     * @return Subgraph
     */
    public function subgraph($id)
    {
        $instruction = $this->instructions[] = new Subgraph($id, $this);

        return $instruction;
    }

    /**
     * Created a new node on graph.
     *
     * @param string $id Identifier of node
     *
     * @param array $attributes Attributes to set on node
     *
     * @return Graph Fluid-interface
     */
    public function node($id, array $attributes = array())
    {
        $this->instructions[] = new Node($id, $attributes, $this);

        return $this;
    }

    /**
     * Created a new node on graph.
     *
     * @param string $id Identifier of node
     *
     * @param array $attributes Attributes to set on node
     *
     * @return Node
     */
    public function beginNode($id, array $attributes = array())
    {
        $instruction = $this->instructions[] = new Node($id, $attributes, $this);

        return $instruction;
    }

    /**
     * Created a new edge on graph.
     *
     * @param array $list List of edges
     *
     * @param array $attributes Attributes to set on edge
     *
     * @return Graph Fluid-interface
     */
    public function edge($list, array $attributes = array())
    {
        $this->instructions[] = $this->createEdge($list, $attributes, $this);

        return $this;
    }

    /**
     * Created a new edge on graph.
     *
     * @param array $list List of edges
     *
     * @param array $attributes Attributes to set on edge
     *
     * @return Edge
     */
    public function beginEdge($list, array $attributes = array())
    {
        $instruction = $this->instructions[] = $this->createEdge($list, $attributes, $this);

        return $instruction;
    }

    /**
     * Fluid-interface to access parent.
     *
     * @return Graph
     */
    public function end()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function render($indent = 0, $spaces = self::DEFAULT_INDENT)
    {
        $margin = str_repeat($spaces, $indent);
        $result = $margin.$this->getHeader($this->id).' {'."\n";
        foreach ($this->instructions as $instruction) {
            $result .= $instruction->render($indent + 1, $spaces);
        }
        $result .= $margin.'};'."\n";

        return $result;
    }
}