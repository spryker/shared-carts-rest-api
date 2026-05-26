<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\SharedCartsRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\SharedCartsStorefrontResource;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\QuotePermissionGroupTransfer;
use Generated\Shared\Transfer\RestSharedCartsAttributesTransfer;
use Generated\Shared\Transfer\ShareCartRequestTransfer;
use Generated\Shared\Transfer\ShareCartResponseTransfer;
use Generated\Shared\Transfer\ShareDetailTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\SharedCartsRestApi\SharedCartsRestApiClientInterface;
use Spryker\Glue\SharedCartsRestApi\Api\Storefront\Exception\SharedCartsExceptionFactory;
use Spryker\Glue\SharedCartsRestApiExtension\Dependency\Plugin\CompanyUserProviderPluginInterface;
use Spryker\Service\Container\Attributes\Plugin;
use Spryker\Service\Serializer\SerializerServiceInterface;

class SharedCartsStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string URI_VAR_CART_ID = 'cartId';

    protected const string URI_VAR_UUID = 'uuid';

    public function __construct(
        protected SharedCartsRestApiClientInterface $sharedCartsRestApiClient,
        protected SharedCartsExceptionFactory $exceptionFactory,
        protected SerializerServiceInterface $serializer,
        #[Plugin(dependencyProviderMethod: 'getCompanyUserProviderPlugin')]
        protected CompanyUserProviderPluginInterface $companyUserProviderPlugin,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): SharedCartsStorefrontResource
    {
        $cartUuid = $this->resolveCartUuid();
        $restSharedCartsAttributesTransfer = $this->buildRestSharedCartsAttributesTransfer($data);

        $companyUserTransfer = $this->lookupCompanyUser((string)$restSharedCartsAttributesTransfer->getIdCompanyUser());

        if ($companyUserTransfer->getIdCompanyUser() === null) {
            throw $this->exceptionFactory->createCompanyUserNotFoundException();
        }

        $currentIdCompany = $this->getCustomer()->getCompanyUserTransfer()?->getFkCompany();

        if ($currentIdCompany === null || $currentIdCompany !== $companyUserTransfer->getFkCompany()) {
            throw $this->exceptionFactory->createShareCartOutsideTheCompanyForbiddenException();
        }

        $shareCartRequestTransfer = (new ShareCartRequestTransfer())
            ->addShareDetail(
                (new ShareDetailTransfer())
                    ->setQuotePermissionGroup(
                        (new QuotePermissionGroupTransfer())
                            ->setIdQuotePermissionGroup((int)$restSharedCartsAttributesTransfer->getIdCartPermissionGroup()),
                    )
                    ->setIdCompanyUser($companyUserTransfer->getIdCompanyUser()),
            )
            ->setQuoteUuid($cartUuid)
            ->setCustomerReference($this->getCustomerReference());

        $shareCartResponseTransfer = $this->sharedCartsRestApiClient->create($shareCartRequestTransfer);

        if (!$shareCartResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromShareCartResponse($shareCartResponseTransfer);
        }

        return $this->mapShareDetailToResource($this->getFirstShareDetail($shareCartResponseTransfer));
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPatch(mixed $data): SharedCartsStorefrontResource
    {
        $sharedCartUuid = $this->resolveSharedCartUuid();
        $restSharedCartsAttributesTransfer = $this->buildRestSharedCartsAttributesTransfer($data);

        $shareCartRequestTransfer = (new ShareCartRequestTransfer())
            ->addShareDetail(
                (new ShareDetailTransfer())
                    ->setQuotePermissionGroup(
                        (new QuotePermissionGroupTransfer())
                            ->setIdQuotePermissionGroup((int)$restSharedCartsAttributesTransfer->getIdCartPermissionGroup()),
                    )
                    ->setUuid($sharedCartUuid),
            )
            ->setCustomerReference($this->getCustomerReference());

        $shareCartResponseTransfer = $this->sharedCartsRestApiClient->update($shareCartRequestTransfer);

        if (!$shareCartResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromShareCartResponse($shareCartResponseTransfer);
        }

        return $this->mapShareDetailToResource($this->getFirstShareDetail($shareCartResponseTransfer));
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processDelete(): ?object
    {
        $sharedCartUuid = $this->resolveSharedCartUuid();

        $shareCartRequestTransfer = (new ShareCartRequestTransfer())
            ->addShareDetail((new ShareDetailTransfer())->setUuid($sharedCartUuid))
            ->setCustomerReference($this->getCustomerReference());

        $shareCartResponseTransfer = $this->sharedCartsRestApiClient->delete($shareCartRequestTransfer);

        if (!$shareCartResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromShareCartResponse($shareCartResponseTransfer);
        }

        return null;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function resolveCartUuid(): string
    {
        $cartUuid = $this->getUriVariables()[static::URI_VAR_CART_ID] ?? null;

        if (!is_string($cartUuid) || $cartUuid === '') {
            throw $this->exceptionFactory->createCartIdMissingException();
        }

        return $cartUuid;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function resolveSharedCartUuid(): string
    {
        $uuid = $this->getUriVariables()[static::URI_VAR_UUID] ?? null;

        if (!is_string($uuid) || $uuid === '') {
            throw $this->exceptionFactory->createSharedCartIdMissingException();
        }

        return $uuid;
    }

    protected function lookupCompanyUser(string $companyUserUuid): CompanyUserTransfer
    {
        return $this->companyUserProviderPlugin->provideCompanyUser(
            (new CompanyUserTransfer())->setUuid($companyUserUuid),
        );
    }

    protected function getFirstShareDetail(ShareCartResponseTransfer $shareCartResponseTransfer): ShareDetailTransfer
    {
        $shareDetails = $shareCartResponseTransfer->getShareDetails();

        return $shareDetails->offsetExists(0) ? $shareDetails->offsetGet(0) : new ShareDetailTransfer();
    }

    protected function mapShareDetailToResource(ShareDetailTransfer $shareDetailTransfer): SharedCartsStorefrontResource
    {
        return $this->serializer->denormalize(
            [
                'uuid' => $shareDetailTransfer->getUuid(),
                'idCompanyUser' => $shareDetailTransfer->getCompanyUser()?->getUuid(),
                'idCartPermissionGroup' => $shareDetailTransfer->getQuotePermissionGroup()?->getIdQuotePermissionGroup(),
            ],
            SharedCartsStorefrontResource::class,
        );
    }

    protected function buildRestSharedCartsAttributesTransfer(mixed $data): RestSharedCartsAttributesTransfer
    {
        if ($data instanceof RestSharedCartsAttributesTransfer) {
            return $data;
        }

        $payload = $data instanceof SharedCartsStorefrontResource
            ? get_object_vars($data)
            : (array)$data;

        return (new RestSharedCartsAttributesTransfer())->fromArray(
            array_filter($payload, static fn ($value): bool => $value !== null),
            true,
        );
    }
}
