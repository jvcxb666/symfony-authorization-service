<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByAnyCredit(array $data): array|null
    {
        if(empty($data['email']) && empty($data['phone']) && empty($data['username'])) return null;

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select("u")->from("App\Entity\User","u");
        if(!empty($data['email'])) $qb->andWhere("u.email = '{$data['email']}'");
        if(!empty($data['phone'])) $qb->andWhere("u.phone = '{$data['phone']}'");
        if(!empty($data['username'])) $qb->andWhere("u.username = '{$data['username']}'");
        $qb->setMaxResults(1);

        return $qb->getQuery()->getArrayResult()[0] ?? null;
    }

    public function deleteById(string $id): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete("App\Entity\User","u")
            ->andWhere("u.id = '{$id}'")
                ->getQuery()
                    ->execute();
    }
}
