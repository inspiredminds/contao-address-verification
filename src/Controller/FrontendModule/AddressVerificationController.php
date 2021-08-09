<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Haste\Form\Form;
use InspiredMinds\ContaoAddressVerification\AddressVerifier;
use InspiredMinds\ContaoAddressVerification\Event\BuildAddressVerificationFormEvent;
use InspiredMinds\ContaoAddressVerification\Model\AddressGroupModel;
use InspiredMinds\ContaoAddressVerification\Model\AddressModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Terminal42\NodeBundle\NodeManager;

/**
 * @FrontendModule(AddressVerificationController::TYPE, category="address_verification")
 */
class AddressVerificationController extends AbstractFrontendModuleController
{
    public const TYPE = 'address_verification';

    private $eventDispatcher;
    private $nodeManager;
    private $verifier;

    public function __construct(EventDispatcherInterface $eventDispatcher, NodeManager $nodeManager, AddressVerifier $verifier)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->nodeManager = $nodeManager;
        $this->verifier = $verifier;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): Response
    {
        $form = $this->buildForm($module, $request);

        if ($form->validate()) {
            $this->setAddressSession($request, $form->getBoundModel());

            $addressGroupIds = array_map('\intval', StringUtil::deserialize($module->address_groups, true));
            $verified = $this->verifier->verify($form->getBoundModel(), $addressGroupIds, (bool) $module->address_country);
            $template->isVerified = $verified;
            $template->isUnverified = !$verified;
            $moduleVar = 'address_'.($verified ? 'verified' : 'unverified').'_';
            $template->class .= ' is-'.($verified ? 'verified' : 'unverified');

            if (!empty($module->{$moduleVar.'redirect'}) && (null !== ($jumpTo = PageModel::findByPk($module->{$moduleVar.'redirect'})))) {
                return new RedirectResponse($jumpTo->getAbsoluteUrl());
            }

            if (!empty($module->{$moduleVar.'nodes'}) && !empty($nodeIds = array_map('\intval', StringUtil::deserialize($module->{$moduleVar.'nodes'}, true)))) {
                $template->nodes = $this->nodeManager->generateMultiple($nodeIds);
            }

            if ($verified) {
                $groupNodeIds = [];

                foreach ($addressGroupIds as $addressGroupId) {
                    $addressGroup = AddressGroupModel::findByPk($addressGroupId);
                    $groupNodeIds = array_unique(array_merge($groupNodeIds, StringUtil::deserialize($addressGroup->nodes, true)));
                }

                if (!empty($groupNodeIds)) {
                    $template->groupNodes = $this->nodeManager->generateMultiple($groupNodeIds);
                }
            }
        } else {
            $template->form = $form->generate('form_address_verification');
            $template->formId = $this->getFormId($module);
            $template->addresses = $this->getAddresses($module);
        }

        return new Response($template->parse());
    }

    private function buildForm(ModuleModel $module, Request $request): Form
    {
        $form = new Form($this->getFormId($module), 'POST', function (Form $form) use ($request) {
            return $request->request->get('FORM_SUBMIT') === $form->getFormId();
        });

        $form->addFieldsFromDca('tl_address', function (string $field, array $def) use ($module) {
            if (empty($def['inputType'])) {
                return false;
            }

            if (!(bool) $module->address_country && 'country' === $field) {
                return false;
            }

            return true;
        });

        $form->addSubmitFormField('submit', 'Submit');

        $address = new AddressModel();
        $address->preventSaving(false);
        $form->bindModel($address);

        $this->eventDispatcher->dispatch(new BuildAddressVerificationFormEvent($form));

        return $form;
    }

    private function getFormId(ModuleModel $model): string
    {
        return 'address-verification-'.$model->id;
    }

    /**
     * @return array<AddressModel>
     */
    private function getAddresses(ModuleModel $module): array
    {
        $addresses = AddressModel::findByPids(StringUtil::deserialize($module->address_groups, true));

        if (null === $addresses) {
            return [];
        }

        Controller::loadDataContainer('tl_address');
        $dcaFields = $GLOBALS['TL_DCA']['tl_address']['fields'];

        return array_map(
            function (AddressModel $address) use ($dcaFields, $module) {
                $row = $address->row();

                foreach ($row as $key => $value) {
                    if (empty($dcaFields[$key]['inputType'])) {
                        unset($row[$key]);
                    }
                }

                // Build a human readable address for the autocomplete selection
                $row['address'] = $address->getReadableAddress((bool) $module->address_country);

                return $row;
            }, $addresses->getModels()
        );
    }

    private function setAddressSession(Request $request, AddressModel $address): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();
        $session->set('address-verification', $address->row());
    }
}
