<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Glue\SharedCartsRestApi\Helper;

use Codeception\Module;
use Generated\Shared\Transfer\RestCartsAttributesTransfer;
use Generated\Shared\Transfer\RestSharedCartsAttributesTransfer;
use PHPUnit\Framework\Assert;
use Spryker\Glue\SharedCartsRestApi\SharedCartsRestApiConfig;
use Spryker\Shared\Price\PriceConfig;
use SprykerTest\ApiPlatform\Helper\ApiRequestHelperTrait;
use SprykerTest\Glue\CompanyUsersRestApi\Helper\CompanyUserDataHelper;
use SprykerTest\Shared\Store\Helper\StoreDataHelperTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * The two company users a share needs, the cart it hangs off, and the requests sent against the
 * shared-carts resource.
 */
class SharedCartDataHelper extends Module
{
    use ApiRequestHelperTrait;
    use StoreDataHelperTrait;

    protected const string URI_CARTS = '/carts';

    protected const string URI_SHARED_CARTS = '/shared-carts';

    protected const string URI_CART_PERMISSION_GROUPS = '/cart-permission-groups';

    protected const string CART_NAME = 'Storefront API shared cart';

    /**
     * An approved company with two company users, the first of them authenticated for the next
     * request. Every share names a second company user, so this pair is the minimum identity any
     * scenario needs.
     *
     * @return array{0: \Generated\Shared\Transfer\CompanyUserTransfer, 1: \Generated\Shared\Transfer\CompanyUserTransfer}
     */
    public function arrangeAuthenticatedOwnerAndColleague(): array
    {
        $companyUserDataHelper = $this->getCompanyUserDataHelper();

        $companyTransfer = $companyUserDataHelper->haveApprovedCompany();
        $ownerTransfer = $companyUserDataHelper->haveCompanyUserForNewCustomerInCompany($companyTransfer);
        $colleagueTransfer = $companyUserDataHelper->haveCompanyUserForNewCustomerInCompany($companyTransfer);

        $companyUserDataHelper->authenticateAsCompanyUser($ownerTransfer);

        return [$ownerTransfer, $colleagueTransfer];
    }

    /**
     * Creates the cart through `POST /carts` rather than the Quote facade: the facade raises a
     * messenger message this lane has no Zed translator container for.
     */
    public function arrangeCart(): string
    {
        $response = $this->handleApiRequest(
            'POST',
            static::URI_CARTS,
            $this->encodeJsonApiBody(SharedCartsRestApiConfig::RESOURCE_CARTS, [
                RestCartsAttributesTransfer::PRICE_MODE => PriceConfig::PRICE_MODE_GROSS,
                RestCartsAttributesTransfer::CURRENCY => $this->getStoreDataHelper()->getCurrentCurrencyCode(),
                RestCartsAttributesTransfer::STORE => $this->getStoreDataHelper()->getCurrentStoreName(),
                RestCartsAttributesTransfer::NAME => static::CART_NAME,
            ]),
        );

        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode(), (string)$response->getContent());

        return $this->readId($response);
    }

    /**
     * A cart of the authenticated owner, already shared with a colleague.
     *
     * @return array{0: string, 1: string, 2: string} The shared-cart uuid, the colleague's company-user uuid and the cart uuid.
     */
    public function arrangeSharedCartForAuthenticatedOwner(): array
    {
        [, $colleagueTransfer] = $this->arrangeAuthenticatedOwnerAndColleague();
        $cartUuid = $this->arrangeCart();

        $response = $this->postSharedCart($cartUuid, [
            RestSharedCartsAttributesTransfer::ID_COMPANY_USER => $colleagueTransfer->getUuidOrFail(),
            RestSharedCartsAttributesTransfer::ID_CART_PERMISSION_GROUP => $this->readFirstCartPermissionGroupId(),
        ]);

        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode(), (string)$response->getContent());

        return [$this->readId($response), $colleagueTransfer->getUuidOrFail(), $cartUuid];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function postSharedCart(string $cartUuid, array $attributes): Response
    {
        return $this->handleApiRequest(
            'POST',
            $this->cartSharedCartsUri($cartUuid),
            $this->encodeSharedCartBody($attributes),
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function patchSharedCart(string $sharedCartUuid, array $attributes): Response
    {
        return $this->handleApiRequest(
            'PATCH',
            $this->sharedCartUri($sharedCartUuid),
            $this->encodeSharedCartBody($attributes, $sharedCartUuid),
        );
    }

    public function deleteSharedCart(string $sharedCartUuid): Response
    {
        return $this->handleApiRequest('DELETE', $this->sharedCartUri($sharedCartUuid));
    }

    /**
     * For the cases about a body the resource cannot decode at all, which no attribute array can
     * express.
     */
    public function postSharedCartWithRawBody(string $cartUuid, string $body): Response
    {
        return $this->handleApiRequest('POST', $this->cartSharedCartsUri($cartUuid), $body);
    }

    public function patchSharedCartWithRawBody(string $sharedCartUuid, string $body): Response
    {
        return $this->handleApiRequest('PATCH', $this->sharedCartUri($sharedCartUuid), $body);
    }

    public function getCartPermissionGroups(): Response
    {
        return $this->handleApiRequest('GET', static::URI_CART_PERMISSION_GROUPS);
    }

    /**
     * The permission groups come from the installer rather than from a fixture, so the identifier
     * a share needs is read off the catalogue the API itself publishes.
     */
    public function readFirstCartPermissionGroupId(): int
    {
        return (int)$this->readCartPermissionGroupIds()[0];
    }

    /**
     * The other end of the catalogue, for the case that changes a share's permission group to one
     * it does not already carry.
     */
    public function readLastCartPermissionGroupId(): int
    {
        $ids = $this->readCartPermissionGroupIds();

        return (int)$ids[array_key_last($ids)];
    }

    /**
     * @return array<int|string, string>
     */
    protected function readCartPermissionGroupIds(): array
    {
        $response = $this->getCartPermissionGroups();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string)$response->getContent());

        /** @var array{data: array<array{id: string}>} $document */
        $document = json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return array_column($document['data'], 'id');
    }

    protected function readId(Response $response): string
    {
        /** @var array{data: array{id: string, attributes: array<string, mixed>}} $document */
        $document = json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $document['data']['id'];
    }

    protected function sharedCartUri(string $sharedCartUuid): string
    {
        return sprintf('%s/%s', static::URI_SHARED_CARTS, $sharedCartUuid);
    }

    protected function cartSharedCartsUri(string $cartUuid): string
    {
        return sprintf('%s/%s%s', static::URI_CARTS, $cartUuid, static::URI_SHARED_CARTS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function encodeSharedCartBody(array $attributes, ?string $uuid = null): string
    {
        return $this->encodeJsonApiBody(SharedCartsRestApiConfig::RESOURCE_SHARED_CARTS, $attributes, $uuid);
    }

    protected function getCompanyUserDataHelper(): CompanyUserDataHelper
    {
        /** @var \SprykerTest\Glue\CompanyUsersRestApi\Helper\CompanyUserDataHelper $companyUserDataHelper */
        $companyUserDataHelper = $this->getModule('\\' . CompanyUserDataHelper::class);

        return $companyUserDataHelper;
    }
}
