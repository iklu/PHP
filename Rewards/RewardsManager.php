<?php
namespace Acme\DataBundle\Model\Rewards;
use Doctrine\Common\Persistence\ObjectManager;

use Acme\DataBundle\Entity\ClutchReferences;

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 12.07.2016
 * Time: 16:28
 */
class  RewardsManager implements RewardsManagerInterface
{

    protected $objectManager;
    protected $class;
    protected $repository;
    protected $rewards;


    /**
     * RewardsManager constructor.
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->objectManager = $om;
    }

    /**
     * Returns an empty rewards instance
     *
     * @return RewardsManagerInterface
     */
    public function createRewards($class)
    {
        $this->class = $class;
        $this->repository =  $this->objectManager->getRepository($class);

        $metadata =  $this->objectManager->getClassMetadata($class);
        $this->class = $metadata->getName();

        $class = $this->getClass();
        $this->rewards = new $class;

        return $this->rewards;
    }

    /**
     * Find a user by registered user id
     *
     * @param integer $userId
     *
     * @return RewardsManagerInterface
     */
    public function findUserById($userId) {
        return $this->repository->findById($userId);
    }
	
	/**
	 * Find a user by registered card number
	 *
	 * @param string $cardNumber
	 *
	 * @return RewardsManagerInterface
	 */
	public function findUserByCardNumber($cardNumber) {
		return $this->repository->findByCardNumber($cardNumber);
	}

    /**
     * Find a reward by registered user
     *
     * @param integer $userId
     *
     * @return RewardsManagerInterface
     */
    public function findRewardsByRegisteredUser($userId)
    {
        return $this->repository->findByRegisteredUser($userId);
    }

    /**
     * Finds a reward by referral user
     *
     * @param integer $userId
     *
     * @return RewardsManagerInterface
     */
    public function findRewardsByReferralUser($userId)
    {
        return $this->repository->findByReferralUser($userId);
    }

    /**
     * Finds a reward by firstTransaction
     *
     * @param string $firstTransaction
     *
     * @return UserInterface
     */
    public function findRewardsByFirstTransaction($firstTransaction)
    {
        return $this->repository->findByFirstTransaction($firstTransaction);
    }

    /**
     * ReferralCounter
     * Search the referral counter by referral code (user phone)
     *
     * @param $referralCode
     * @return mixed
     */
    public function findByReferralCodeCounter($referralCode)
    {
        return $this->repository->findOneByPhone($referralCode);
    }

    /**
     * Users
     * Search the referral user by  (user phone)
     *
     * @param $referralCode
     * @return mixed
     */
    public function findByReferralUser($referralCode)
    {
        return $this->repository->findOneBy(array('phone'=>$referralCode, 'enabled'=>true));
    }

    /**
     * {@inheritDoc}
     */
    public function updateFirstTransaction(RewardsInterface $reward)
    {
        $reward->setFirstTransaction($reward->getFirstTransaction());
    }


    /**
     * {@inheritDoc}
     */
    public function deleteReward(RewardsInterface $reward)
    {
        $this->objectManager->remove($reward);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function findRewardByCriteria(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findRewards()
    {
        return $this->repository->findAll();
    }

    public function findByPromoCode($promoCode) {
        return $this->repository->findOneByPromoCode($promoCode);
    }
    
  
    /**
     * Updates a reward.
     *
     * @param RewardsInterface $rewards
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateRewards(RewardsInterface $rewards, $andFlush = true)
    {
        $this->objectManager->persist($rewards);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * @return mixed
     */
    public function getEntity(){
        return $this->rewards;
    }

    /**
     * Updates a reward.
     *
     * @param CounterInterface $counter
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateCounter(CounterInterface $counter, $andFlush = true)
    {
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * Updates a reward.
     *
     * @param CounterInterface $counter
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function createCounter(CounterInterface $counter, $andFlush = true)
    {
        $this->objectManager->persist($counter);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }
    
    /**
     * @return mixed
     */
    public function checkCardInClutch($promoCode){
        
        
        return $this->rewards;
    }
}