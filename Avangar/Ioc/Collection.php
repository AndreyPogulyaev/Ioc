<?php

namespace Avangar\Ioc;

class Collection
{
    private $types = ['new', 'single', 'lazy'];
    private $defaultType = 'single';

    private $primary;
    private $type;
    private $arg = array(); // Arguments for __construct
    private $autoCalls = array();
    private $instance;

    public function __construct(string $primary, string $type = null, array $args = null, $autoCalls = null)
    {
        $this->primary = $primary;
        $this->type = $this->getType($type);
        if (is_array($args)) {
            foreach($args as $key => $value) {
                $this->arg($key, $value);
            }
        }
        if (is_array($autoCalls)) {
            foreach($autoCalls as $key => $value) {
                $this->autoCall($key, $value);
            }
        }
        return $this;
    }

    public function type(string $type = null): Collection
    {
        $this->type = $this->getType($type);
        return $this;
    }

    public function arg(string $parameter, $argument): Collection
    {
        $this->arg[$parameter] = $argument;
        return $this;
    }

    public function autoCall(string $methodName, array $args = null): Collection
    {
        $this->autoCalls[$methodName] = $args;
        return $this;
    }

    public function delAutoCall(string $methodName): Collection
    {
        if (array_key_exists($methodName, $this->autoCalls)) {
            unset($this->autoCalls[$methodName]);
        }
        return $this;
    }

    public function setInstance(object $Obj)
    {
        $this->instance = $Obj;
    }

    public function getInstance(): object
    {
        return $this->instance;
    }

    public function hasInstance(): bool
    {
        return is_object($this->instance);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'arg':
                return $this->arg;
                break;
            case 'type':
                return $this->type;
                break;
            case 'autoCalls':
                return $this->autoCalls;
                break;
        }

    }

    public function clear()
    {
        $this->type = $this->defaultType;
        $this->arg = [];
        $this->autoCalls = [];
    }

    public function clearArg()
    {
        $this->arg = [];
    }

    public function clearAutoCall()
    {
        $this->autoCalls = [];
    }

    private function getType($type): string
    {
        if (in_array($type, $this->types)) {
            return $type;
        } else {
            return $this->defaultType;
        }
    }
}