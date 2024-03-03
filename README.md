# Transactions local



## Test Task 


Write a method that accepts a list of transactions and user balance

```
type Transaction = {
    id: number;
    orderId: number;
    amount: number;
    txType: 'Bet' | 'Win';
｝
```

Return an arbitrary structure where each transaction is marked as valid or invalid.

Transactions are processed from smallest id to largest.

Bet reduces the balance by an amount, Win increases it.

If the balance goes into minus, the transaction is considered invalid.

If a transaction is not valid, subsequent transactions with the same orderId are also considered invalid.

If a transaction id is repeated, that transaction is also invalid, but others with the same orderId must be processed.

# Solution

I decided to implement DDD to solve this task; 

Also, this test task is for experiments with gitlabCI.

# GitlabCI

I've created pipeline to check quality code from different utilities (PHPCS, PHPStan, Deptrac) and start tests.

