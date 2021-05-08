<?php

namespace pingi\trontrxapi\Interfaces;

use pingi\trontrxapi\Account;
use pingi\trontrxapi\Address;
use pingi\trontrxapi\Block;
use pingi\trontrxapi\Transaction;

/**
 * Interface WalletInterface
 * @package pingi\trontrxapi\Interfaces
 */
interface WalletInterface
{
    public function generateAddress(): Address;

    public function validateAddress(Address $address): bool;

    public function getAccount(Address $address): ?Account;

    public function getAccountNet(Address $address): ?array;

    public function easyTransferByPrivate(string $private, Address $address, float $amount);

    public function createTransaction(Address $toAddress, Address $ownerAddress, float $amount = 0): Transaction;

    public function signTransaction(Transaction &$transaction, string $privateKey): Transaction;

    public function broadcastTransaction(Transaction $transaction): bool;

    public function getTransactionById(string $transactionID): Transaction;

    public function getNowBlock(): Block;

    public function getBlockById(string $blockId): Block;

//    public function freezeBalance(Address $ownerAddress, float $balanceToFreeze, int $durationDays, string $resource = 'BANDWIDTH');
}