<?php

namespace App\Repository;

use App\Entity\Token;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Token>
 */
class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    public function deleteUserTokens(string $user_id): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete("App\Entity\Token","t")
            ->andWhere("t.user_id = '{$user_id}'")
                ->getQuery()
                    ->execute();
    }
}
