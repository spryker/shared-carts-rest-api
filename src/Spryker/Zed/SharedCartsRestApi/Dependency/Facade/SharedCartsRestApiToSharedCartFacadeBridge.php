<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SharedCartsRestApi\Dependency\Facade;

use Generated\Shared\Transfer\QuoteCompanyUserTransfer;
use Generated\Shared\Transfer\QuotePermissionGroupResponseTransfer;
use Generated\Shared\Transfer\QuotePermissionGroupTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShareCartRequestTransfer;
use Generated\Shared\Transfer\ShareCartResponseTransfer;
use Generated\Shared\Transfer\ShareDetailCollectionTransfer;
use Generated\Shared\Transfer\ShareDetailCriteriaFilterTransfer;

class SharedCartsRestApiToSharedCartFacadeBridge implements SharedCartsRestApiToSharedCartFacadeInterface
{
    /**
     * @var \Spryker\Zed\SharedCart\Business\SharedCartFacadeInterface
     */
    protected $sharedCartFacade;

    /**
     * @param \Spryker\Zed\SharedCart\Business\SharedCartFacadeInterface $sharedCartFacade
     */
    public function __construct($sharedCartFacade)
    {
        $this->sharedCartFacade = $sharedCartFacade;
    }

    public function createQuoteCompanyUser(ShareCartRequestTransfer $shareCartRequestTransfer): ShareCartResponseTransfer
    {
        return $this->sharedCartFacade->createQuoteCompanyUser($shareCartRequestTransfer);
    }

    public function getShareDetailsByIdQuote(QuoteTransfer $quoteTransfer): ShareDetailCollectionTransfer
    {
        return $this->sharedCartFacade->getShareDetailsByIdQuote($quoteTransfer);
    }

    public function findQuotePermissionGroupById(QuotePermissionGroupTransfer $quotePermissionGroupTransfer): QuotePermissionGroupResponseTransfer
    {
        return $this->sharedCartFacade->findQuotePermissionGroupById($quotePermissionGroupTransfer);
    }

    public function deleteQuoteCompanyUser(ShareCartRequestTransfer $shareCartRequestTransfer): void
    {
        $this->sharedCartFacade->deleteQuoteCompanyUser($shareCartRequestTransfer);
    }

    public function updateQuoteCompanyUserPermissionGroup(ShareCartRequestTransfer $shareCartRequestTransfer): ShareCartResponseTransfer
    {
        return $this->sharedCartFacade->updateQuoteCompanyUserPermissionGroup($shareCartRequestTransfer);
    }

    public function findQuoteCompanyUserByUuid(QuoteCompanyUserTransfer $quoteCompanyUserTransfer): ?QuoteCompanyUserTransfer
    {
        return $this->sharedCartFacade->findQuoteCompanyUserByUuid($quoteCompanyUserTransfer);
    }

    public function getShareDetailCollectionByShareDetailCriteria(
        ShareDetailCriteriaFilterTransfer $shareDetailCriteriaFilterTransfer
    ): ShareDetailCollectionTransfer {
        return $this->sharedCartFacade->getShareDetailCollectionByShareDetailCriteria($shareDetailCriteriaFilterTransfer);
    }
}
