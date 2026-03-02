<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\SharedCartsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\ShareDetailTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface SharedCartRestResponseBuilderInterface
{
    public function createSharedCartRestResponse(?ShareDetailTransfer $shareDetailTransfer = null): RestResponseInterface;

    public function createErrorResponseFromErrorIdentifier(string $errorIdentifier): RestResponseInterface;

    public function createCartIdMissingErrorResponse(): RestResponseInterface;

    public function createCompanyUserNotFoundErrorResponse(): RestResponseInterface;

    public function createSharingForbiddenErrorResponse(): RestResponseInterface;

    public function createSharedCartIdMissingErrorResponse(): RestResponseInterface;
}
