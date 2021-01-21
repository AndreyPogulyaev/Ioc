<?php

namespace Avangar\Ioc;

class Config
{
    public $collections = [];
    private $dependency = [];

    public function set(string $primary, string $type = null, array $args = null, $autoCalls = null): Collection 
    {
        if ($this->isExistsCollection($primary)){ //redefine all
            $Collection = $this->collections[$primary];
            if ($type) {
                $Collection->type($type);
            }
            if (is_array($args)) {
                foreach($args as $key => $value) {
                    $Collection->arg($key, $value);
                }
            }
            if (is_array($autoCalls)) {
                foreach($autoCalls as $key => $value) {
                    $Collection->autoCall($key, $value);
                }
            }
        } else {
            $this->collections[$primary] = new Collection($primary, $type, $args, $autoCalls);
        }
        return $this->collections[$primary];
    }

    public function getCollection($name)
    {
        return (!$this->isExistsCollection($name))? false : $this->collections[$name];
    }

    /**
     * @param <required> string $group //Имя группы
     * @param <required> string $name //Имя интерфейса
     * @return iIocCollection
     */
    public function setRelativeGroup($group, $name)
    {
        return $this->collections[$name] = clone $this->initCollection($group);
    }

    /**
     * @param <required> string $interfaceName <Имя интерфейса>
     * @param <required> string $className <Имя класса>
     * @return void
     */
    public function setDependency($interfaceName, $className)
    {
        $this->dependency[$interfaceName] = $className;
    }

    /**
     * @param <required> string $interfaceName
     * @return string OR boolean(false)
     */
    public function getDependency($interfaceName)
    {
        return (array_key_exists($interfaceName, $this->dependency)) ? $this->dependency[$interfaceName] : false;
    }

    /**
     * @param string $className
     * @return string $interfaceName OR false
     */
    public function getDependentInterfaceName($className)
    {
        foreach ($this->dependency as $key => $value) {
            if ($value == $className) {
                return $key;
            }
        }
        return false;
    }



    /**
     * @param <required> string $name Interface name OR class name
     * @param <required> object $Obj
     * @return void
     */
    public function setInstance($name, $Obj)
    {
        $this->initCollection($name);
        $this->collections[$name]->setInstance($Obj);
    }

    /**
     * @return object
     */
    public function getInstance($name)
    {
        if ($this->hasInstance($name)) {
            return $this->collections[$name]->getInstance();
        } else {
            throw new \Exception('IocConfig::getInstance()  - Не удается получить экземпляр класса [' . $name . ']', 001);
        }
    }

    public function hasInstance($name): bool
    {
        if (!$this->isExistsCollection($name)) {
            return false;
        } else {
            return $this->collections[$name]->hasInstance();
        }
    }

    public function initCollection($name): object
    {
        if (!$this->isExistsCollection($name))  $this->collections[$name] = new Collection($name);
        return $this->collections[$name];
    }

    private function isExistsCollection($name): bool
    {
        return array_key_exists($name, $this->collections);
    }
}