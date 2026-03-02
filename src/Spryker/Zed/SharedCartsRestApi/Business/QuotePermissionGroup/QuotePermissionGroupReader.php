<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SharedCartsRestApi\Business\QuotePermissionGroup;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\SharedCartsRestApi\Dependency\Facade\SharedCartsRestApiToSharedCartFacadeInterface;

class QuotePermissionGroupReader implements QuotePermissionGroupReaderInterface
{
    /**
     * @var \Spryker\Zed\SharedCartsRestApi\Dependency\Facade\SharedCartsRestApiToSharedCartFacadeInterface
     */
    protected $sharedCartFacade;

    public function __construct(SharedCartsRestApiToSharedCartFacadeInterface $sharedCartFacade)
    {
        $this->sharedCartFacade = $sharedCartFacade;
    }

    public function expandQuoteWithQuotePermissionGroup(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        if (!$this->isQuoteExpandable($quoteTransfer)) {
            return $quoteTransfer;
        }

        $shareDetailCollectionTransfer = $this->sharedCartFacade->getShareDetailsByIdQuote($quoteTransfer);
        foreach ($shareDetailCollectionTransfer->getShareDetails() as $shareDetailTransfer) {
            if ($shareDetailTransfer->getIdCompanyUser() === $quoteTransfer->getCustomer()->getCompanyUserTransfer()->getIdCompanyUser()) {
                $quoteTransfer->setQuotePermissionGroup($shareDetailTransfer->getQuotePermissionGroup());

                return $quoteTransfer;
            }
        }

        return $quoteTransfer;
    }

    protected function isQuoteExpandable(QuoteTransfer $quoteTransfer): bool
    {
        return $quoteTransfer->getCustomerReference() !== $quoteTransfer->getCustomer()->getCustomerReference()
            && $quoteTransfer->getCustomer()->getCompanyUserTransfer()
            && $quoteTransfer->getCustomer()->getCompanyUserTransfer()->getIdCompanyUser();
    }
}
