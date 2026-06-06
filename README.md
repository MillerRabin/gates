# Gates

Create .env file from env.example and fill database credentials

## launch locally
  php artisan serve

## launch as docker container


## Reorganization strategy

For every indexed block the system stores:

- block_number
- block_hash
- parent_hash

Before indexing a new block the indexer verifies:

previous_block.block_hash == current_block.parent_hash

If hashes do not match, a blockchain reorganization is detected.

Rollback strategy:

1. Find the last common ancestor block.
2. Delete indexed_blocks after that ancestor.
3. Delete deposits created from orphaned blocks.
4. Re-index blocks from the last valid ancestor.
