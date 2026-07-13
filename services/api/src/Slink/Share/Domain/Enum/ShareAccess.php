<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Enum;

enum ShareAccess: string {
  case Create = 'share.create';
  case Edit = 'share.edit';
  case Unlock = 'share.unlock';
}
