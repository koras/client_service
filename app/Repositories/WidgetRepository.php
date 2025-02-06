<?php

namespace App\Repositories;

use App\Contracts\Models\WidgetInterface;
use  App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Models\Widget;
use App\Contracts\DTO\ArrayableDtoInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class BaseRepository
 * @package App\Repositories
 *
 * @method Collection|Model[] all()
 * @method bool existsById(int|string $id)
 * @method array getExistingIds(array $needleIds)
 * @method Collection|array findInField(string $fieldName, array $values)
 * @method Model find(int|string $id)
 * @method Collection|array findBy(array $params)
 * @method Model|null findOneBy(array $params)
 * @method Model create(ArrayableDtoInterface $dto)
 * @method Model update(int|string $id, array $params)
 * @method bool delete(int|string $id)
 */
class WidgetRepository extends BaseRepository implements WidgetRepositoryInterface
{

    public function getWidgetsShortList() {
        return $this->createQueryBuilder('widget')
            ->select('widget.id', 'widget.name')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

//    public function findWidgetByDomainOrId_old(string $term=null)
//    {
//
//        $qb = $this->createQueryBuilder("widget");
//        try {
//            $uuid = Uuid::fromString($term);
//            $qb->orWhere('widget.id = :term_uuid')
//                ->setParameter('term_uuid', $uuid);
//        } catch (\Exception $exception) {}
//        return $qb
//            ->orWhere('widget.domain = :term')
//            ->setParameter('term', $term)
//            ->getQuery()
//            ->getOneOrNullResult();
//    }

    public function findWidgetByDomainOrId(string $term): ?WidgetInterface
    {
        try {
            if (Str::isUuid($term)) {
                $widget = Widget::where('id', $term)->firstOrFail();
            } else {
                $widget = Widget::where('domain', $term)->orWhere('domain', $term)->firstOrFail();
            }
        } catch (ModelNotFoundException $exception) {
            $widget = null;
        }

        return $widget;
    }

    public function findCover(string $cover)
    {
        $queryBuilder = $this->createQueryBuilder('widget');
        return $queryBuilder
            ->select("widget.id")
            ->where("widget.covers LIKE :value")
            ->setParameter('value', '%'.$cover.'%')
            ->getQuery()
            ->getResult();
    }

    public function getUserWidgets(string $user_id)
    {
        $sql = 'select widget_id from widget_user_widget where widget_user_id = :user_id';
        $widget_ids = $this->entityManager
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['user_id' => $user_id])
            ->fetchAllAssociative();

        $queryBuilder = $this->createQueryBuilder('widgets');

        return $queryBuilder
            ->select("widgets")
            ->where("widgets.id IN (:widget_ids)")
            ->setParameter('widget_ids', $widget_ids)
            ->getQuery()
            ->getResult();
    }
    // Add other methods for CRUD operations or custom queries


    public function getOrderServiceDsn(): ?string
    {
        return
            parse_url($this->order_service_dsn_url, PHP_URL_SCHEME) . '://' .
            $this->order_service_dsn_login . ':' .
            $this->order_service_dsn_password . '@' .
            parse_url($this->order_service_dsn_url, PHP_URL_HOST) .
            parse_url($this->order_service_dsn_url, PHP_URL_PATH);
    }
}
