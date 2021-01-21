<?php      

namespace Avangar\Ioc;

class Ioc
{
    protected $defaultInstanceType = "new";

    /**
     * @var Config
     */
    protected $Config;

    /**
     * @var mixed
     */
    protected $lastResult;

    /**
     * @param Config $Config
     */
    public function __construct(Config $Config)
    {
        $this->Config = $Config;
        $this->Config->setInstance('Avangar\Ioc\Ioc', $this);
        $this->Config->setInstance('Avangar\Ioc\Config', $Config);
    }

    /**
     * @param $name string
     * @return object
     */
    public function get($name)
    {
        /*
        if ($this->is_interface($name)) {
            $className = $this->Config->getDependency($name);
            $interfaceName = $name;
        } else {
            $className = $name;
            $interfaceName = "";
        }
        */
        $depen = $this->Config->getDependency($name);
        if ($depen) {
            $className = $depen;
            $interfaceName = $name;
        } else {
            $className = $name;
            $interfaceName = "";
        }
        $instanceType = ($this->Config->getCollection($name)) ? $this->Config->getCollection($name)->type : $this->defaultInstanceType;
        return $this->instanceOnMethod($instanceType, $className, $interfaceName);
    }

    /**
     * @param string $className
     * @param string $methodName
     */
    public function invoke($className, $methodName, array $arg = null)
    {
        return $this->reflectionRun($className, $methodName, null, $arg);
    }

    public function set(... $args): Collection {
        return $this->Config->set(... $args);
    }

    public function delegate($interfaceName, $className)
    {
        $this->Config->setDependency($interfaceName, $className);
    }


    /**
     * @param string $instanceType
     * @param string $interfaceName
     * @param string $className
     */
    protected function instanceOnMethod($instanceType, $className, $interfaceName)
    {
        $instanceMethod = "Instance" . $instanceType;
        if (!method_exists(__CLASS__, $instanceMethod)) {
            throw new \Exception("Указанный для [$interfaceName] тип инстацирования [$instanceType]($instanceMethod) — не найден.", 1002);
        }
        return $this->$instanceMethod($className, $interfaceName);
    }

    /**
     * @param $name
     * @return object
     */
    private function InstanceNew($className, $interfaceName)
    {
        $instanceName = ($interfaceName == '') ? $className : $interfaceName;
        if (!$this->reflectionRun($className, null, $interfaceName)) {
                $this->Config->setInstance($instanceName, new $className());
        }
        $isAutoCalls = false;
        if (!$this->runAutoCalls($instanceName, $instanceName) && $className != $instanceName) {
            $this->runAutoCalls($className, $instanceName);
        }
        return $this->Config->getInstance($instanceName);
    }

    private function runAutoCalls($instanceName, $className) {
        $Collect = $this->Config->getCollection($instanceName);
        if ($Collect) {
            $autoCalls = $Collect->autoCalls;
            if (is_array($autoCalls) && count($autoCalls) > 0 ) {
                $isAutoCalls = true;
                foreach($autoCalls as $methodName => $args) {
                    $this->invoke($this->Config->getInstance($className), $methodName, $args);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $classname
     */
    private function InstanceSingle($className, $interfaceName)
    {
        $instanceType = ($interfaceName == '') ? $className : $interfaceName;
        if (!$this->Config->hasInstance($instanceType)) {
            return $this->InstanceNew($className, $interfaceName);
        } else {
            return $this->Config->getInstance($instanceType);
        }
    }

    /**
     * @param string $classname
     */
    public function InstanceLazyInit($className, $interfaceName)
    {
        $instanceType = ($interfaceName == '') ? $className : $interfaceName;
        if (!$this->Config->hasInstance($instanceType)) {
            return $this->InstanceNew($className, $interfaceName);
        } else {
            return $this->Config->getInstance($instanceType);
        }
    }

    /**
     * @param string $interfaceName
     * @return string
     */
    protected function getDependentClassName($interfaceName)
    {
        if (!$this->Config->getDependency($interfaceName)) {
            throw new \Exception("Не определена зависимость к интерфейсу [$interfaceName]", 100);
        } else {
            return $this->Config->getDependency($interfaceName);
        }
    }

    /**
     * @param string $name
     * @return boolean
     */
    protected function is_interface($name)
    {
        $ref = new \ReflectionClass($name);
        return $ref->isInterface();
    }


    public function lastResult()
    {
        return $this->lastResult;
    }

    /**
     * @param unknown_type $className
     * @param unknown_type $methodName
     * @param unknown_type $interfaceName
     */
    private function reflectionRun($className, $methodName = "__construct", $interfaceName = '', array $args = null)
    {
        if (!$methodName) {
            $methodName = "__construct";
        }
        if (!is_object($className)) {
            if (!class_exists($className)) {
                return false;
            }
            $reflect = new \ReflectionClass($className);
            $new = true;
        } else {
            $reflect = new \ReflectionObject($className);
            $new = false;
        }
        $interfaceRealization = array();
        if ($reflect->hasMethod($methodName) && $reflect->getMethod($methodName)->getNumberOfParameters() > 0) {
            $interfaceRealization = $this->iteratorInstance($reflect->getMethod($methodName)->getParameters(), $className, $args);
            if ($new) {
                $instanceName = (!$interfaceName) ? $className : $interfaceName;
                $this->Config->setInstance($instanceName, $reflect->newInstanceArgs($interfaceRealization));
            } else {
                $instanceName = (!$interfaceName) ? $reflect->getName() : $interfaceName;
                $this->lastResult = $reflect->getMethod($methodName)->invokeArgs($className, $interfaceRealization);
                //$this->Config->setInstance($instanceName, $reflect->getMethod($methodName)->invokeArgs($className, $interfaceRealization));
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $parameters
     */
    private function iteratorInstance($parameters, $className, array $argsAdv = null)
    {
        $methodArgs = [];
        $args = [];

        if (is_object($className)) {
            $className = get_class($className);
        }

        $Collect = $this->Config->getCollection($className);
        if ($Collect) {
            $args = $Collect->arg;
        } else {
            $depentClassName = $this->Config->getDependentInterfaceName($className);
            if ($depentClassName) {
                $CollectDepent = $this->Config->getCollection($depentClassName);
                if ($CollectDepent) {
                    $args = $CollectDepent->arg;
                }
            }
        }
        if (is_array($argsAdv) && count($argsAdv) > 0) {
            $args = array_merge($args, $argsAdv);
        }

        foreach ($parameters as $key => $value) {
            $matches = array();
            $paramClass = $value->getClass();
            if (is_object($paramClass)) {
                $methodArgs[] = $this->get($paramClass->getName());
            } else {
                $paramName = $value->getName();
                $arguments = (isset($args[$paramName]))? $args[$paramName] : null;
                
                if (!empty($arguments)) {
                    $methodArgs[] = $arguments;
                } else if (!$value->isOptional()) {
                    throw new \Exception("Class [{$className}] - [{$paramName}]", 102);
                } else {
                    //if ($value->getDefaultValue()){
                    //$args[] = $value->getDefaultValue();
                    //}
                }
            }
        }
        return $methodArgs;
    }
}