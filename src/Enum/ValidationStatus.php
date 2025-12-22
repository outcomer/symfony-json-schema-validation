<?php
/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation\Enum
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Enum;

/**
 * Validation status enumeration
 */
enum ValidationStatus
{
	case VALID;
	case INVALID;
}
