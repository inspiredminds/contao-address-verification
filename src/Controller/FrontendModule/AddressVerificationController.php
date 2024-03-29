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

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use FOS\HttpCache\ResponseTagger;
use Haste\Form\Form;
use InspiredMinds\ContaoAddressVerification\AddressVerifier;
use InspiredMinds\ContaoAddressVerification\Controller\AddressController;
use InspiredMinds\ContaoAddressVerification\Event\BuildAddressVerificationFormEvent;
use InspiredMinds\ContaoAddressVerification\Model\AddressGroupModel;
use InspiredMinds\ContaoAddressVerification\Model\AddressModel;
use InspiredMinds\ContaoAddressVerification\ProcessAddressesTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Terminal42\NodeBundle\NodeManager;

/**
 * @FrontendModule(AddressVerificationController::TYPE, category="address_verification")
 */
class AddressVerificationController extends AbstractFrontendModuleController
{
    use ProcessAddressesTrait;

    public const TYPE = 'address_verification';

    private $eventDispatcher;
    private $nodeManager;
    private $verifier;
    private $urlGenerator;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        NodeManager $nodeManager,
        AddressVerifier $verifier,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->nodeManager = $nodeManager;
        $this->verifier = $verifier;
        $this->urlGenerator = $urlGenerator;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): Response
    {
        $form = $this->buildForm($module, $request);

        $addressGroupIds = array_map('\intval', StringUtil::deserialize($module->address_groups, true));

        if ($form->validate()) {
            $address = $form->getBoundModel();
            $this->setAddressSession($request, $address);

            $verified = $this->verifier->verify($address, $addressGroupIds, (bool) $module->address_country);
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
                $allGroupNodeIds = [];
                $groupNodesMeta = [];

                foreach ($addressGroupIds as $addressGroupId) {
                    $addressGroup = AddressGroupModel::findByPk($addressGroupId);
                    $groupNodeIds = StringUtil::deserialize($addressGroup->nodes, true);
                    $allGroupNodeIds = array_unique(array_merge($allGroupNodeIds, $groupNodeIds));
                    $verifiedGroup = (int) $address->pid === $addressGroupId;

                    $groupNodesMeta[] = [
                        'verified' => (int) $address->pid === $addressGroupId,
                        'content' => implode("\n", $this->nodeManager->generateMultiple($groupNodeIds)),
                        'class' => 'address-group-content address-group-content--'.$addressGroup->id.($verifiedGroup ? ' address-group-content--verified' : ''),
                    ];
                }

                if (!empty($allGroupNodeIds)) {
                    $template->groupNodes = $this->nodeManager->generateMultiple($allGroupNodeIds);
                }

                $template->groupNodesMeta = $groupNodesMeta;
            }
        } else {
            $addresses = AddressModel::findByPids($addressGroupIds);

            $template->form = $form->generate('form_address_verification');
            $template->formId = $this->getFormId($module);
            $template->addresses = $this->processAddresses($addresses, (bool) $module->address_country);
            $template->asyncUrl = $this->urlGenerator->generate(AddressController::class, ['module' => $module->id, '_locale' => $request->getLocale()]);

            $this->tagResponse($addresses);
            $this->tagResponse(array_map(static function ($id) { return 'contao.db.tl_address_group.'.$id; }, $addressGroupIds));
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

    private function setAddressSession(Request $request, AddressModel $address): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();
        $session->set('address-verification', $address->row());
    }
}
