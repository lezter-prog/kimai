<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class QuickEntryModel extends Constraint
{
    public const ACTIVITY_REQUIRED = 'quick-entry-model-01';
    public const PROJECT_REQUIRED = 'quick-entry-model-02';
    public const DESCRIPTION_REQUIRED = 'quick-entry-model-03';

    public string $messageActivityRequired = 'An activity is required field.';
    public string $messageProjectRequired = 'A project needs to be selected.';
    public string $messageDescriptionRequired = 'A Description is required field.';
}
