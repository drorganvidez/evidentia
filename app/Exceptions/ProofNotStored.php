<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A proof of an evidence could not be moved to its folder or saved; the evidence goes back to its form.
 */
final class ProofNotStored extends RuntimeException {}
