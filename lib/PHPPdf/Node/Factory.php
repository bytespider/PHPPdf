<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace PHPPdf\Node;

use PHPPdf\Util\UnitConverter;

use PHPPdf\Exception\UnregisteredNodeException;

/**
 * Factory of the nodes based on Factory Method and Prototype design pattern
 *
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class Factory implements \Serializable
{
    private $prototypes = array();
    private $invocationsMethodsOnCreate = array();
    private $invokeArgs = array();

    public function addPrototype($name, Node $node, array $invocationsMethodsOnCreate = array())
    {
        $name = (string) $name;

        $this->prototypes[$name] = $node;
        $this->invocationsMethodsOnCreate[$name] = $invocationsMethodsOnCreate;
    }
    
    public function addPrototypes(array $prototypes)
    {
        foreach($prototypes as $name => $node)
        {
            $this->addPrototype($name, $node);
        }
    }
    
    /**
     * Adds method and argument tag to invoke after creating
     * 
     * @see create()
     * 
     * @param string $name Name of prototype
     * @param string $invocationMethodName Name of setter method
     * @param string $invocationMethodArgId Argument id, {@see addInvokeArg()}
     */
    public function addInvocationsMethodsOnCreate($name, $invocationMethodName, $invocationMethodArgId)
    {
        $this->invocationsMethodsOnCreate[$name][$invocationMethodName] = $invocationMethodArgId;
    }
    
    /**
     * Adds argument witch can be used as argument of setter method on factory products
     * 
     * @param string $tag Tag of argument
     * @param mixed $value Value of argument
     */
    public function addInvokeArg($tag, $value)
    {
        $this->invokeArgs[$tag] = $value;
    }
    
    public function getInvokeArgs()
    {
        return $this->invokeArgs;
    }
    
    public function invocationsMethodsOnCreate()
    {
        return $this->invocationsMethodsOnCreate;
    }

    /**
     * Create copy of node stored under passed name
     *
     * @param string Name/key of prototype
     * 
     * @return Node Deep copy of node stored under passed name
     * @throws PHPPdf\Exception\UnregisteredNodeException If prototype with passed name dosn't exist
     */
    public function create($name)
    {
        $prototype = $this->getPrototype($name);

        $product = $prototype->copy();
        
        foreach($this->invocationsMethodsOnCreate[$name] as $methodName => $argTag)
        {
            if(isset($this->invokeArgs[$argTag]))
            {
                $arg = $this->invokeArgs[$argTag];
                $product->$methodName($arg);
            }
        }

        return $product;
    }

    /**
     * @return PHPPdf\Node\Node
     * @throws PHPPdf\Exception\UnregisteredNodeException If prototype with passed name dosn't exist
     */
    public function getPrototype($name)
    {
        $name = (string) $name;

        if(!$this->hasPrototype($name))
        {
            UnregisteredNodeException::nodeNotRegisteredException($name);
        }

        return $this->prototypes[$name];
    }

    public function hasPrototype($name)
    {
        $name = (string) $name;

        return isset($this->prototypes[$name]);
    }

    public function serialize()
    {
        return serialize(array(
            'prototypes' => $this->prototypes,
            'invocationsMethodsOnCreate' => $this->invocationsMethodsOnCreate,
            'invokeArgs' => $this->invokeArgs,
        ));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        
        $prototypes = $data['prototypes'];
        $invocationsMethodsOnCreate = $data['invocationsMethodsOnCreate'];
        $invokeArgs = $data['invokeArgs'];

        foreach($prototypes as $name => $prototype)
        {
            $invocationsMethods = isset($invocationsMethodsOnCreate[$name]) ? $invocationsMethodsOnCreate[$name] : array();
            $this->addPrototype($name, $prototype, $invocationsMethods);
        }   

        foreach($invokeArgs as $tag => $value)
        {
            $this->addInvokeArg($tag, $value);
        }
    }
}