<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\SharedCartsRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\CartPermissionGroupsStorefrontResource;
use Generated\Api\Storefront\SharedCartsStorefrontResource;
use Generated\Shared\Transfer\QuotePermissionGroupTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShareDetailTransfer;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\Client\SharedCartsRestApi\SharedCartsRestApiClientInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

/**
 * Builds `SharedCarts` sub-resources for a `Carts` parent. Mirrors the legacy
 * {@see \Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\Relationship\SharedCartByCartIdExpander}
 * by fetching share details for each cart UUID via {@see SharedCartsRestApiClientInterface::getSharedCartsByCartUuid()}.
 */
class CartsSharedCartsRelationshipResolver extends AbstractRelationshipResolver
{
    public function __construct(
        protected SharedCartsRestApiClientInterface $sharedCartsRestApiClient,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\SharedCartsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $resources = [];

        foreach ($this->getParentResources() as $parent) {
            $cartUuid = $parent->uuid ?? null;

            if (!is_string($cartUuid) || $cartUuid === '') {
                continue;
            }

            $shareDetailCollectionTransfer = $this->sharedCartsRestApiClient
                ->getSharedCartsByCartUuid((new QuoteTransfer())->setUuid($cartUuid));

            foreach ($shareDetailCollectionTransfer->getShareDetails() as $shareDetailTransfer) {
                if (!$shareDetailTransfer->getUuid()) {
                    continue;
                }

                $resources[] = $this->mapShareDetailToResource($shareDetailTransfer);
            }
        }

        return $resources;
    }

    protected function mapShareDetailToResource(ShareDetailTransfer $shareDetailTransfer): SharedCartsStorefrontResource
    {
        $quotePermissionGroupTransfer = $shareDetailTransfer->getQuotePermissionGroup();

        return $this->serializer->denormalize(
            [
                'uuid' => $shareDetailTransfer->getUuid(),
                'idCompanyUser' => $shareDetailTransfer->getCompanyUser()?->getUuid(),
                'idCartPermissionGroup' => $quotePermissionGroupTransfer?->getIdQuotePermissionGroup(),
                'cartPermissionGroup' => $quotePermissionGroupTransfer === null
                    ? null
                    : $this->buildCartPermissionGroupResource($quotePermissionGroupTransfer),
            ],
            SharedCartsStorefrontResource::class,
        );
    }

    protected function buildCartPermissionGroupResource(
        QuotePermissionGroupTransfer $quotePermissionGroupTransfer
    ): CartPermissionGroupsStorefrontResource {
        return $this->serializer->denormalize(
            [
                'idQuotePermissionGroup' => $quotePermissionGroupTransfer->getIdQuotePermissionGroup(),
                'name' => $quotePermissionGroupTransfer->getName(),
                'isDefault' => $quotePermissionGroupTransfer->getIsDefault(),
            ],
            CartPermissionGroupsStorefrontResource::class,
        );
    }
}
