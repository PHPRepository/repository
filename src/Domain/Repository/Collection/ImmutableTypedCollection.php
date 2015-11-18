<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 6/16/15
 * Time: 1:28 AM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpDdd\Foundation\Domain\Repository\Collection;

use JsonSerializable;
use RuntimeException;
use SplFixedArray;

/**
 * Class ImmutableTypedCollection
 * @package PhpDdd\Foundation\Domain\Repository
 */
final class ImmutableTypedCollection extends SplFixedArray implements JsonSerializable
{
    /**
     * @var string
     */
    private $type;

    /**
     * {@inheritDoc}
     */
    public function offsetSet($index, $newval)
    {
        if (null === $index) {
            $index = (int) $this->calculateIndexKey();
        }

        (null === $this->type) ? $this->setGuardType($newval) : $this->guard($newval);
        parent::offsetSet($index, $newval);
    }

    /**
     * @return int
     */
    private function calculateIndexKey()
    {
        $callable = function ($value) {
            return null !== $value;
        };

        return count(array_filter($this->toArray(), $callable));
    }

    /**
     * @param $newval
     */
    private function setGuardType($newval)
    {
        $type       = gettype($newval);
        $this->type = ('object' === $type) ? get_class($newval) : $type;
    }

    /**
     * @param $newval
     *
     * @throws \RuntimeException
     */
    private function guard($newval)
    {
        $type = gettype($newval);
        ('object' !== $type) ? $this->baseTypeGuard($type) : $this->objectGuard($newval);
    }

    /**
     * @param $newval
     *
     * @throws \RuntimeException
     */
    private function objectGuard($newval)
    {
        $newValClass = get_class($newval);
        if ($newValClass !== $this->type) {
            throw new RuntimeException(
                sprintf(
                    'Provided value must be instance of %s, but instance of %s was given instead.',
                    $this->type,
                    $newValClass
                )
            );
        }
    }

    /**
     * @param $type
     *
     * @throws \RuntimeException
     */
    private function baseTypeGuard($type)
    {
        if ($type !== $this->type) {
            throw new RuntimeException(
                sprintf(
                    'Provided value must be of type %s, but variable of type %s was given instead.',
                    $this->type,
                    $type
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}