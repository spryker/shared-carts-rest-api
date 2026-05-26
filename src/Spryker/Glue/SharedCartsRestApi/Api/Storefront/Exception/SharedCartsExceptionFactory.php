<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\SharedCartsRestApi\Api\Storefront\Exception;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\ShareCartResponseTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\SharedCartsRestApi\SharedCartsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class SharedCartsExceptionFactory
{
    public function __construct(
        protected SharedCartsRestApiConfig $sharedCartsRestApiConfig,
    ) {
    }

    public function createCartIdMissingException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            SharedCartsRestApiConfig::RESPONSE_CODE_CART_ID_MISSING,
            SharedCartsRestApiConfig::EXCEPTION_MESSAGE_CART_ID_MISSING,
        );
    }

    public function createSharedCartIdMissingException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            SharedCartsRestApiConfig::RESPONSE_CODE_SHARED_CART_ID_MISSING,
            SharedCartsRestApiConfig::RESPONSE_DETAIL_SHARED_CART_ID_MISSING,
        );
    }

    public function createCompanyUserNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            SharedCartsRestApiConfig::RESPONSE_CODE_COMPANY_USER_NOT_FOUND,
            SharedCartsRestApiConfig::RESPONSE_DETAIL_COMPANY_USER_NOT_FOUND,
        );
    }

    public function createShareCartOutsideTheCompanyForbiddenException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_FORBIDDEN,
            SharedCartsRestApiConfig::RESPONSE_CODE_SHARE_CART_OUTSIDE_THE_COMPANY_FORBIDDEN,
            SharedCartsRestApiConfig::RESPONSE_DETAIL_SHARE_CART_OUTSIDE_THE_COMPANY_FORBIDDEN,
        );
    }

    public function createSharingForbiddenException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_FORBIDDEN,
            SharedCartsRestApiConfig::RESPONSE_CODE_SHARING_CART_FORBIDDEN,
            SharedCartsRestApiConfig::RESPONSE_DETAIL_SHARING_CART_FORBIDDEN,
        );
    }

    public function createExceptionFromShareCartResponse(ShareCartResponseTransfer $shareCartResponseTransfer): GlueApiException
    {
        $errorIdentifier = (string)$shareCartResponseTransfer->getErrorIdentifier();
        $errorData = $this->sharedCartsRestApiConfig->getErrorIdentifierToRestErrorMapping()[$errorIdentifier] ?? null;

        if ($errorData === null) {
            return new GlueApiException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $errorIdentifier !== '' ? $errorIdentifier : '0',
                $errorIdentifier !== '' ? $errorIdentifier : 'Unknown error.',
            );
        }

        return new GlueApiException(
            (int)$errorData[RestErrorMessageTransfer::STATUS],
            (string)$errorData[RestErrorMessageTransfer::CODE],
            (string)$errorData[RestErrorMessageTransfer::DETAIL],
        );
    }
}
