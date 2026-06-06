# Gates
## Prepare to install
  1. Create .env file from env.example and fill database credentials
  2. Use make install to prepare database and generate hotwallets

## launch locally
  make server

## launch in docker container
  make build -  to build docker image
  make run - to run image

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
