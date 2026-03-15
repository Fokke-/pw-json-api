<?php

namespace PwJsonApi;

/**
 * Paginated API response
 *
 * @see https://pwjsonapi.fokke.fi/responses.html#paginated-responses
 */
class PaginatedResponse extends Response
{
  protected ?int $_start = null;
  protected ?int $_limit = null;
  protected ?int $_total = null;

  /**
   * Set the start offset
   */
  public function start(int $start): static
  {
    $this->_start = $start;
    return $this;
  }

  /**
   * Set the limit (items per page)
   */
  public function limit(int $limit): static
  {
    $this->_limit = $limit;
    return $this;
  }

  /**
   * Set the total number of items
   */
  public function total(int $total): static
  {
    $this->_total = $total;
    return $this;
  }

  /**
   * Get the current page number (1-based)
   */
  public function getPage(): int
  {
    if ($this->_limit === 0) {
      return 1;
    }

    return (int) floor($this->_start / $this->_limit) + 1;
  }

  /**
   * Get the total number of pages
   */
  public function getPages(): int
  {
    if ($this->_limit === 0) {
      return $this->_total > 0 ? 1 : 0;
    }

    return (int) ceil($this->_total / $this->_limit);
  }

  /**
   * Return response as an array
   *
   * @return array<string, mixed>|null
   * @throws \LogicException If start, limit, or total has not been set
   */
  public function toArray(bool $withData = true): ?array
  {
    if (
      $this->_start === null ||
      $this->_limit === null ||
      $this->_total === null
    ) {
      throw new \LogicException(
        'PaginatedResponse requires start, limit, and total to be set',
      );
    }

    parent::with([
      'pagination' => [
        'start' => $this->_start,
        'limit' => $this->_limit,
        'total' => $this->_total,
        'page' => $this->getPage(),
        'pages' => $this->getPages(),
      ],
    ]);

    return parent::toArray($withData);
  }
}
