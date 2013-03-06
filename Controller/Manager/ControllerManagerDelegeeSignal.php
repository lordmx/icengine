<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для after-вызова
 * 
 * @author morph
 */
class ControllerManagerDelegeeSignal extends ControllerManagerDelegeeAbstract
{ 
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations();
        $actionScheme = $scheme->getMethod($context->getAction());
        if (empty($actionScheme['Signal'])) {
            return;
        }
        $controllerManager = $context->getControllerManager();
        $key = $controller->getName() . '/' . $context->getAction();
        $eventManager = $controllerManager->getService('eventManager');
        $signal = $eventManager->getSignal($key);
        $slot = $eventManager->getSlot('Delegee');
        $slot->setParams(array(
            'signal'    => $actionScheme['Signal'],
            'context'   => $context
        ));
        $eventManager->register($signal, $slot);
    }
}