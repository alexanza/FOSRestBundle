<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\EventListener;

use FOS\RestBundle\Model\Patch;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * This listener handles Patch Request.
 * currently only support patch request from json format (RFC 6902)
 *
 * @author alexanza
 */
class PatchRequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequest()->getMethod() != 'PATCH'
            || !$event->isMasterRequest()
            || strpos($event->getRequest()->headers->get('Content-Type'), 'application/json') === false) {
            return;
        }

        $request = $event->getRequest();

        $event->getRequest()->attributes->set(
            'patches',
            $this->getPatches($request->request->all())
        );
    }

    /**
     * @param array $patches this array should contain the json already processed by the BodyListener
     */
    private function getPatches(array $aPatches)
    {
        $patches = array();
        foreach ($aPatches as $patch) {
            if (!$this->validatePatch($patch)) {
                throw new BadRequestHttpException('Invalid patch request received');
            }

            $patches[] = new Patch(
                $patch['op'],
                @$patch['from'],
                @$patch['path'],
                @$patch['value']
            );
        }

        return $patches;
    }

    private function validatePatch(array $patch)
    {
        if (empty($patch['path'])) {
            return false;
        }

        switch ($patch['op']) {
            case 'add':
            case 'replace':
                if (!isset($patch['value'])) {
                    return false;
                }
                break;
            case 'move':
            case 'copy':
                if (empty($patch['from'])) {
                    return false;
                }
                break;
        }

        return true;
    }
}
