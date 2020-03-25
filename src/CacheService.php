<?php

namespace MantuTpService;

use think\facade\Cache;


/**
 * Class TpCacheService
 * @notes: tp缓存服务
 * @version 1.0
 * @author: W_wang
 * @email: 1352255400@qq.com
 * @since: 2020/3/25 16:13
 * @package MantuTpService
 */
class CacheService
{
    //定义表前缀
    private $keyPrefix;
    //定义缓存驱动类型
    private $store;

    public function __construct()
    {
        //初始化缓存配置
        $redisConfig = config('cache.redis');
        //缓存前缀
        $this->keyPrefix = isset($redisConfig['prefix']) && !empty($redisConfig['prefix']) ? '' : 'cache:';
        //指定缓存驱动（redis）
        $this->store = 'redis';
    }

    /**
     * @set:设置缓存
     * @version: 1.0
     * @author: W_wang
     * @email: 1352255400@qq.com
     * @since: 2020/3/25 16:13
     * @return bool
     */
    public function set($key = '', $data = '', $time = 0)
    {
        //检查缓存key
        if (!$data || !$key) return false;
        //初始化缓存时间
        $time = intval($time);
        $time = $time > 0 ? $time : 7200;
        $key = $this->keyPrefix . $key;
        $re = Cache::store($this->store)->set($key, json_encode($data), $time);
        return $re;
    }

    /**
     * @get:获取缓存
     * @version: 1.0
     * @author: W_wang
     * @email: 1352255400@qq.com
     * @since: 2020/3/25 16:13
     * @return mixed
     */
    public function get($key = '')
    {
        //检查缓存key
        if (!$key) return false;
        $key = $this->keyPrefix . $key;
        $re = Cache::store($this->store)->get($key);
        $re = json_decode($re, true);
        return $re;
    }

    /**
     * @delete:删除缓存
     * @version: 1.0
     * @author: W_wang
     * @email: 1352255400@qq.com
     * @since: 2020/3/25 16:13
     * @return bool
     */
    public function delete($key = '')
    {
        //检查缓存key
        if (!$key) return false;
        $key = $this->keyPrefix . $key;
        //销毁缓存
        $re = Cache::store($this->store)->rm($key);
        return $re;
    }


    /**
     * @saveWithKey:分组写入缓存
     * @version: 1.0
     * @author: W_wang
     * @email: 1352255400@qq.com
     * @since: 2020/3/25 16:14
     * @return bool
     */
    public function saveWithKey($primaryKey = '', $key = '', $data = '', $time = 0)
    {
        //检查缓存key
        if (!$primaryKey || !$key) return false;
        //获取子缓存列表
        $arrayVal = $this->get($primaryKey);
        //追加缓存列表
        if (is_array($arrayVal)) {
            if (!in_array($key, $arrayVal)) {
                array_push($arrayVal, $key);
                $re = $this->set($primaryKey, $arrayVal, $time);
                if (!$re) {
                    return $re;
                }
            }
        } else {
            $arrayVal = array($key);
            $re = $this->set($primaryKey, $arrayVal, $time);
            if (!$re) {
                return $re;
            }
        }
        //写入缓存
        $re = $this->set($key, $data, $time);
        return $re;
    }

    /**
     * @delWithKey:分组删除缓存
     * @version: 1.0
     * @author: W_wang
     * @email: 1352255400@qq.com
     * @since: 2020/3/25 16:14
     * @return bool
     */
    public function delWithKey($primaryKey = '')
    {
        //检查缓存key
        if (!$primaryKey) return false;
        //获取子缓存列表
        $arrayVal = $this->get($primaryKey);
        if (is_array($arrayVal)) {
            foreach ($arrayVal as $v) {
                //删除子缓存
                $this->delete($v);
            }
        }
        //删除主缓存
        $re = $this->delete($primaryKey);
        return $re;
    }
}
