<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator;

trigger_error('The Symfony\Component\Validator\ClassBasedInterface interface was deprecated in version 2.7 and will be removed in 3.0. Use Symfony\Component\Validator\Mapping\ClassMetadataInterface instead', E_USER_DEPRECATED);

/**
 * An object backed by a PHP class.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.5, to be removed in Symfony 3.0.
 *             Use {@link Mapping\ClassMetadataInterface} instead.
 */
interface ClassBasedInterface
{
    /**
     * Returns the name of the backing PHP class.
     *
     * @return string The name of the backing class.
     */
    public function getClassName();
}
