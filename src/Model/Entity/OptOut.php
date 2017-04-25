<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * OptOut Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $community_id
 * @property int $product_id
 * @property \Cake\I18n\Time $created
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Community $community
 * @property \App\Model\Entity\Product $product
 */
class OptOut extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}