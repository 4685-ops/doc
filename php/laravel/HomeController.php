<?php

namespace App\Http\Controllers;

use App\Facades\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('benchmark:test1,test2', ['only' => ['hello']]);
    }

    public function hello()
    {
        return "hello world";
    }

    public function dbTest()
    {
        // 原生sql
        $users = DB::select('select * from users');
        $users = DB::select('select * from users where id = ?', [1]);
        $users = DB::select('select * from users where id = :id', ['id' => 1]);
        $ret = DB::insert('insert into users (name,email,password) values (?,?,?)',
            ['tanfan', 'tanfan@163.com', '123456']);
        $ret = DB::update('update users set email = ? where id = ?', ['xxxx@163.com', 2]);
        $ret = DB::delete('delete from users where id = ?', [2]);
        DB::statement('drop table users');

        // 获取结果
        $users = DB::table('users')->where('id', 1)->get();
        $users = DB::table('users')->find(1);
        $users = DB::table('users')->where('id', 1)->first();
        $users = DB::table('users')->where('id', 1)->value('name');
        $users = DB::table('users')->pluck('email')->toArray();
        $users = DB::table('users')->simplePaginate(2);

        // 聚合查询
        $id = DB::table('users')->max('id');
        $id = DB::table('users')->min('id');
        $id = DB::table('users')->avg('id');
        $id = DB::table('users')->count('id');
        $id = DB::table('users')->sum('id');
        $id = DB::table('users')->where('id', 4)->exists();
        $id = DB::table('users')->where('id', 4)->doesntExist();

        // where 语句
        // select * from users where id > 1;
        DB::table('users')->where('id', '>', 1)->dump();
        // select * from users where id <> 1;
        DB::table('users')->where('id', '<>', 1)->dump();
        DB::table('users')->where('id', '!=', 1)->dump();
        // select * from users where name like 'tan%';
        DB::table('users')->where('name', 'like', 'tan%')->dump();
        // select * from users where id > 1 or name like 'tan%';
        DB::table('users')->where('id', '>', 1)->orWhere('name', 'like', 'tan%')->dump();
        // select * from users where id > 1 and (email like '%@163' or name like 'tan%');
        DB::table('users')->where('id', '>', 1)->where(function (Builder $query) {
            $query->where('email', 'like', '%@163')
                ->orWhere('name', 'like', 'tan%');
        })->dump();
        // select * from users where id in (1,3);
        DB::table('users')->whereIn('id', [1, 3])->dump();
        // select * from users where id not in (1,3);
        DB::table('users')->whereNotIn('id', [1, 3])->dump();
        // select * from users where created_at is null;
        DB::table('users')->whereNull('created_at')->dump();
        // select * from users where created_at is not null;
        DB::table('users')->whereNotNull('created_at')->dump();
        // select * from users where `name` = `email`;
        DB::table('users')->whereColumn('name', 'email')->dump();

        // 新增
        $ret = DB::table('users')->insert([
            'name' => 'name1',
            'password' => Hash::make('123456'),
            'email' => 'name1@163.com'
        ]);
        $ret = DB::table('users')->insert([
            [
                'name' => 'name2',
                'password' => Hash::make('123456'),
                'email' => 'name2@163.com'
            ],
            [
                'name' => 'name3',
                'password' => Hash::make('123456'),
                'email' => 'name3@163.com'
            ],
        ]);
        $ret = DB::table('users')->insertOrIgnore([
            'id' => 4,
            'name' => 'name1',
            'password' => Hash::make('123456'),
            'email' => 'name1@163.com'
        ]);
        $ret = DB::table('users')->insertGetId([
            'name' => 'name4',
            'password' => Hash::make('123456'),
            'email' => 'name4@163.com'
        ]);

        // 更新
        $ret = DB::table('users')->where('id', 7)
            ->update(['name' => 'tom', 'email' => 'tom@163.com']);
        $ret = DB::table('users')->updateOrInsert(
            ['id' => 10],
            [
                'name' => 'name6',
                'email' => 'name6@163.com',
                'password' => Hash::make('123')
            ]);
        $ret = DB::table('users')->where('id', '7')
            ->increment('score', 10);
        $ret = DB::table('users')->where('id', '7')
            ->decrement('score', 5);
        // 删除
        $ret = DB::table('users')->where('id', 7)->delete();
        dd($ret);

        // 事务
        // 1.闭包，自动提交、回滚
        $ret = DB::transaction(function () {
            DB::table('users')->where('id', 4)
                ->update(['name' => Str::random()]);
            DB::table('users')->where('id', 5)
                ->update(['name' => Str::random()]);
        });
        // 2.手动，自行提交、回滚
        try {
            DB::beginTransaction();

            DB::table('users')->where('id', 4)
                ->update(['name' => Str::random()]);
            DB::table('users')->where('id', 5)
                ->update(['name' => Str::random()]);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    public function modelTest()
    {
        // 新增
        //        $product = Product::query()->create([
        //            'title'       => '水杯',
        //            'category_id' => 1,
        //            'is_on_sale'  => 1,
        //            'price'       => '1200',
        //            'attr'        => ['高' => '10cm', '容积' => '200ml']
        //        ]);
        //        $ret = Product::query()->insert([
        //            'title'       => '水杯2',
        //            'category_id' => 1,
        //            'is_on_sale'  => 1,
        //            'price'       => '1200',
        //            'attr'        => json_encode(['高' => '10cm', '容积' => '200ml'])
        //        ]);
        //        $product = new Product();
        //        $product->fill([
        //            'title'       => '水杯3',
        //            'category_id' => 1,
        //            'is_on_sale'  => 1,
        //            'price'       => '1200',
        //            'attr'        => ['高' => '10cm', '容积' => '200ml']
        //        ]);
        //        $product->title = '水杯4';
        //        $product->save();
        //        dd($product);

        // 查询检索，参考查询构造器
        // $products = Product::all();
        // $products = Product::query()->get();
        // $products = Product::query()->where('is_on_sale', 1)->get();
        // dd($products);

        // Product::query()->where('id', 1)->update(['is_on_sale' => 0]);
        // $product        = Product::query()->find(1);
        // $product->title = '保温杯';
        // $product->save();
        // dd($product);

        // $product = Product::query()->find(2);
        // $product = Product::withTrashed()->find(2);
        // $product->restore();
        // dd($product);
        // $ret = $product->delete();
        // dd($ret);
    }

    public function collectionTest()
    {
        // 获取值
        // $collect = collect([1, 2, 3]);
        // dd($collect->toArray());
        // dd($collect->all());
        // $collect = collect(['k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3']);
        //        $keys    = $collect->keys()->toArray();
        //        $values  = $collect->values()->toArray();
        //        dd($keys, $values);
        //        dd($collect->last());
        // $collect->only(['k1', 'k2'])->dump();
        // $products->pluck('title')->dump();
        // $products->take(2)->dump();
        // dd($collect, $products);
        // dd($products->toJson());
        // $ret = $products->pluck('title')->implode(',');

        // 聚合运算
        //        $products = Product::all()->pluck('price');
        //        $products->count();
        //        $products->sum();
        //        $products->average();
        //        $products->max();
        //        $products->min();

        // 查找判断
        // $exists = collect(['v1', 'v2', 'v3'])->contains('v4');
        // dd($exists);
        // array_diff
        // collect([1, 2, 3])->diff([2, 3])->dd();
        // $collect = collect(['k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3']);
        // $is      = $collect->has('k1');
        // $collect = collect([]);
        //        foreach ($collect as $item) {
        //        }
        // dd($collect->isEmpty());
        // $products = Product::all();
        // $pro      = $products->where('id', 3);
        // dd($pro);

        // 遍历
        // $products = Product::all();
        //        $products->each(function ($item) {
        //            var_dump($item->id);
        //        });
        //        $ret = $products->map(function ($item) {
        //            return $item->id;
        //        });
        // dd($products, $ret->toArray());
        // $keyBy = $products->keyBy('id')->toArray();
        // dd($products->toArray(), $keyBy);
        // $group = $products->groupBy('category_id');
        // dd($group->toArray());
        //        $products->filter(function ($item) {
        //            return $item->id > 3;
        //        })->dd();

        // 对数组本身进行操作的方法
        // $collect = collect(['k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3']);
        // dd($collect->flip()->toArray());
        // dd($collect->reverse()->toArray());
        // collect([12, 4, 5, 2, 77])->sortDesc()->dd();
        //        $products = Product::all();
        //        $products->sortByDesc(function ($product) {
        //            return $product->price;
        //        })->dd();
        // collect(['k1', 'k2'])->combine(['v1', 'v2'])->dd();
        // collect(['k1', 'k2'])->crossJoin(['v1', 'v2'])->dd();


    }

    public function cacheTest()
    {
        // 添加缓存
        Cache::put('key1', 'value1', 10);
        Cache::put('key2', 'value2');
        Cache::put('key3', 'value3', now()->addMinutes(1));

        // 获取缓存
        $v1 = Cache::get('key1', 'default1');
        $v2 = Cache::get('key2', 'default2');
        $v3 = Cache::get('key3', 'default3');
        $is = Cache::has('key3');

        // 如果key存在，则存储失败
        $is = Cache::add('key2', 'value', 10);
        $is = Cache::add('key4', 'value4', 10);

        // 永久存储
        Cache::forever('key5', 'value5');

        // 删除缓存
        Cache::forget('key2');
        Cache::put('key5', '', 0);

        // 计数
        Cache::increment('key6', 1);
        Cache::increment('key6', 1);
        Cache::decrement('key6', 2);
        $v6 = Cache::get('key6');

        // 获取并删除
        Cache::forever('key7', 'value7');
        $v7 = Cache::pull('key7');

        // 获取缓存，缓存失效自动获取数据
        Cache::remember('key8', 60, function () {
            // todo ...
            return ['xxx'];
        });

        //        $cache = Cache::get('key8');
        //        if (is_null($cache)) {
        //            // todo ...
        //            $cache = ['xxx'];
        //            Cache::put('key8', $cache, 60);
        //        }
    }

    public function facadeTest()
    {
        Product::getProduct(123);
    }

}
