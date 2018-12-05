<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    //查询接口
    public function get(Request $request)
    {
        $id = $request->get('uid');
        $user = User::find($id);

        try {
            $type = $user->type;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'code'=>404,
                'msg'=>'没有找到用户',
            ]);
        }

        // 主管查询
        if ($type == 2) {
            $users = User::all();
            //专员查询
        } elseif($type == 1){
            $users = User::where('pid', $id)->get();
        } else{
            return response()->json([
                'code'=>'403',
                'msg'=>'没有相关权限'
            ]);
        }
        return response()->json([
            'code'=>200,
            'data'=>$users
        ]);
    }

    //新增用户接口
    public function store(Request $request)
    {
        $id = $request->post('uid');
        $user = User::find($id);
        if (!$user) {
            return $this->responseJson(404,'没有相关用户');
        }
        $type = $user->type;
        // 主管操作
        if ($type == 2) {
            User::create($request->only([
                'pid',
                'name',
                'sex',
                'phone',
                'address',
                'remark',
                'type',
            ]));
            return $this->responseJson(200,'创建成功');
            // 专员操作
        } elseif ($type == 1) {
            $data = $request->only([
                'name',
                'sex',
                'phone',
                'address',
                'remark',
            ]);
            $data['pid'] = $id;
            User::create($data);
            return $this->responseJson(200,'创建成功');
        } else {
            return $this->responseJson(403,'权限错误');
        }
    }

    // 修改资料接口
    public function update(Request $request)
    {
        // 登陆用户信息
        $id = $request->post('uid');
        $user = User::find($id);

        // 要修改的用户的信息
        $normal_user_id = $request->post('normal_user_id');
        if (!$user || !$normal_user_id) {
            return $this->responseJson(404,'没有相关用户');
        }

        $type = $user->type;
        // 主管操作
        if ($type ==2) {
            $normal_user = User::find($normal_user_id);

            $normal_user->update($request->only([
                'pid',
                'name',
                'sex',
                'phone',
                'address',
                'remark',
            ]));
            return $this->responseJson(200,'更新成功');
            // 专员操作
        } elseif ($type == 1) {
            $normal_user = User::where('id',$normal_user_id)->where('pid',$id);
            if ($normal_user) {
                $normal_user->update($request->only([
                    'name',
                    'sex',
                    'phone',
                    'address',
                    'remark',
                ]));
                return $this->responseJson(200,'更新成功');
            }
            return $this->responseJson(200,'更新成功');
        } else {
            return $this->responseJson(403,'权限错误');
        }
    }

    private function responseJson($code,$data)
    {
        return response()->json([
            'code' => $code,
            'data' => $data,
        ]);
    }
}
