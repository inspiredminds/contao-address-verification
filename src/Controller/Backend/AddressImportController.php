<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification\Controller\Backend;

use Contao\System;
use Doctrine\DBAL\Connection;
use InspiredMinds\ContaoBackendFormsBundle\Form\BackendForm;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @Route("/contao/address-verification/import/{groupId}",
 *   name=AddressImportController::class,
 *   defaults={"_scope": "backend"}
 * )
 */
class AddressImportController
{
    private $translator;
    private $router;
    private $twig;
    private $db;

    public function __construct(TranslatorInterface $translator, RouterInterface $router, Environment $twig, Connection $db)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->twig = $twig;
        $this->db = $db;
    }

    public function __invoke(Request $request, int $groupId): Response
    {
        $form = new BackendForm('address-import-form', 'POST', function (BackendForm $form) use ($request): bool {
            return $request->request->get('FORM_SUBMIT') === $form->getFormId();
        });

        $form->addFormField('file', [
            'label' => [
                $this->translator->trans('CSV file'),
                $this->translator->trans('Please choose the CSV file.'),
            ],
            'inputType' => 'upload',
            'eval' => ['mandatory' => true, 'extensions' => 'csv'],
        ]);

        $form->addSubmitFormField('submit', $this->translator->trans('Import'));

        if ($form->validate()) {
            $this->importCsv($_SESSION['FILES']['file']['tmp_name'], $groupId);

            return new RedirectResponse($this->getBackUrl($request, $groupId), Response::HTTP_SEE_OTHER);
        }

        return new Response($this->twig->render('@ContaoAddressVerification/address_import.html.twig', [
            'form' => $form->generate(),
            'back' => $this->getBackUrl($request, $groupId),
        ]));
    }

    private function getBackUrl(Request $request, int $groupId): string
    {
        return $this->router->generate('contao_backend', [
            'do' => 'address_verification',
            'table' => 'tl_address',
            'id' => $groupId,
            'ref' => $request->attributes->get('_contao_referer_id'),
        ]);
    }

    private function importCsv(string $file, int $groupId): void
    {
        /** @var Reader $csv */
        $csv = Reader::createFromPath($file, 'r');
        $countries = array_keys(System::getCountries());

        foreach ($csv->getRecords() as $record) {
            $country = $record[5] ?? null;

            if (null !== $country) {
                $country = strtolower($country);

                if (!\in_array($country, $countries, true)) {
                    $country = null;
                }
            }

            $addressRecord = array_filter([
                'pid' => $groupId,
                'street' => $record[0],
                'number' => $record[1],
                'apartment' => $record[2],
                'postal' => $record[3],
                'city' => $record[4] ?? null,
                'country' => $country,
            ]);

            $where = implode(' AND ', array_map(function ($value) {
                return $value.' = ?';
            }, array_keys($addressRecord)));

            $exists = (bool) $this->db->fetchOne("SELECT * FROM tl_address WHERE $where", array_values($addressRecord));

            if ($exists) {
                continue;
            }

            $this->db->insert('tl_address', $addressRecord);
        }
    }
}
