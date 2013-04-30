<?php
namespace Baum;

trait Reloadable {
  public function reload() {
    $fresh = $this->newQuery()->find($this->getKey());

    $this->setRawAttributes($fresh->getAttributes(), true);
    return $this;
  }
}
