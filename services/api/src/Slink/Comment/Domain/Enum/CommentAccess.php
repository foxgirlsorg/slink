<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Enum;

enum CommentAccess: string {
  case View = 'comment.view';
  case Create = 'comment.create';
}
