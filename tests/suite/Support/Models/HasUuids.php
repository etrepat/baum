<?php

namespace Baum\Tests\Support\Models;

use Baum\Tests\Support\Observers\ClusterObserver;

trait HasUuids
{
    /**
     * Boot the trait for a model.
     *
     * @return void
     */
    public static function bootHasUuids()
    {
        static::observe(new ClusterObserver);
    }

    /**
     * Initialize the HasUuids trait for an instance.
     *
     * @return void
     */
    public function initializeHasUuids()
    {
        $this->incrementing = false;
    }

    /**
     * Ensures the uuid value is present on the model.
     *
     * @return void
     */
    public function ensureUuid()
    {
        if (is_null($this->{$this->getUuidColumn()})) {
            $this->{$this->getUuidColumn()} = $this->newUuid();
        }
    }

    /**
     * Get the name of the uuid column.
     *
     * @return string
     */
    public function getUuidColumn()
    {
        return $this->getKeyName();
    }

    /**
     * Get the 'qualified' name of the uuid column.
     *
     * @return string
     */
    public function getQualifiedUuidColumn()
    {
        return $this->getQualifiedKeyName();
    }

    /**
     * Return a new UUIDv4 value
     *
     * @return string
     */
    public function newUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
