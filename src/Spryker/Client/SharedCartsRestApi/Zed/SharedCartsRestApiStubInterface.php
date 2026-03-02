<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\SharedCartsRestApi\Zed;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShareCartRequestTransfer;
use Generated\Shared\Transfer\ShareCartResponseTransfer;
use Generated\Shared\Transfer\ShareDetailCollectionTransfer;

interface SharedCartsRestApiStubInterface
{
    public function getSharedCartsByCartUuid(QuoteTransfer $quoteTransfer): ShareDetailCollectionTransfer;

    public function create(ShareCartRequestTransfer $shareCartRequestTransfer): ShareCartResponseTransfer;

    public function update(ShareCartRequestTransfer $shareCartRequestTransfer): ShareCartResponseTransfer;

    public function delete(ShareCartRequestTransfer $shareCartRequestTransfer): ShareCartResponseTransfer;
}
