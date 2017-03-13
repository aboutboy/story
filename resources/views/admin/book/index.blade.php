@extends('layout.admin')

@section('content')
<table class="table table-bordered table-hover bg-white text-center">
    <tr>
        <td width="50">编号</td>
        <td width="100">栏目</td>
        <td width="150" align="left">标题</td>
        <td>最新</td>
        <td>作者</td>
        <td>字数</td>
        <td>关注人数</td>
        <td>浏览量</td>
        <td>添加时间</td>
        <td>更新时间</td>
        <td width="250">操作</td>
    </tr>
    @if(count($lists) > 0)
        @foreach($lists as $v)
            <tr id="book_{{ $v['id'] }}">
                <td>{{ $v['id'] }}</td>
                <td>{{ $categorys[$v['catid']]['name'] }}</td>
                <td align="left"><strong>{{ $v['title'] }}</strong></td>
                <td>{{ $v['zhangjie'] }}</td>
                <td>{{ $v['author'] }}</td>
                <td>{{ $v['wordcount'] }}</td>
                <td>{{ $v['follow'] }}</td>
                <td>{{ $v['hits'] }}</td>
                <td>{{ $v['created_at'] }}</td>
                <td>{{ $v['updated_at'] }}</td>
                <td>
                    <button class="btn btn-sm btn-success" onclick="getDetailLists({{ $v['id'] }})">章节</button>
                    <button class="btn btn-sm btn-info" id="edit_{{ $v['id'] }}" data="{{ json_encode($v) }}" onclick="Edit({{ $v['id'] }})">编辑</button>
                    <button class="btn btn-sm btn-danger" onclick="Delete({{ $v['id'] }})">删除</button>
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="11">
                {!! $lists->render() !!}
            </td>
        </tr>
    @else
        <tr>
            <td colspan="11">
                未找到数据
            </td>
        </tr>
    @endif
</table>
<button class="btn btn-success" data-toggle="modal" data-target="#createModal">添加菜单</button>
<script>

    var dlistsModal = '#DetailListsModal';
    var createModal = '#createModal';
    var updateModal = '#updateModal';
    var deleteModal = '#deleteModal';

    function Delete(id , name)
    {
        name = name ? name : 'id';
        $(deleteModal).find('input[name='+name+']').val(id);
        $(deleteModal).modal('show');
    }

    function Edit(id)
    {
        var json = $('#edit_' + id).attr('data');
        json = JSON.parse(json);
        $.each(json , function(k , v){
//            if( k == 'catid' )
//            {
//                $(updateModal).find('select[name=' + k + ']').val(v);
//            }
//            else if( k == 'introduce' )
//            {
//                $(updateModal).find('textarea[name=' + k + ']').val(v);
//            }
//            else
//            {
                $(updateModal).find('[name=' + k + ']').val(v);
//            }
        });

        $(updateModal).modal('show');
    }

    function ShowDetail(id){
        loading();
        $.get("{!! route('Book.getDetail') !!}" , {id:id} ,function(res){
            layer.open({
                title:'详情',
                area:'800px',
                btn:false,
                shadeClose:true,
                content:res.content,
                success:function(){
                    loading(true);
                }
            });
        });
    }

    function UpdateDetail(id) {
        window.open("{!! route('Book.getUpdateDetail') !!}?id=" + id);
    }

    function DeleteDetail(id) {
        window.open("{!! route('Book.getDeleteDetail') !!}?id=" + id);
    }

    function showData(id,res){
        if(res.data.length){
            var tr = '';
            $.each(res.data , function(k,v){
                tr += '<tr><td>' + v.id + '</td><td align="left"><strong>' + v.title + '</strong></td><td>' + v.hits + '</td><td>' + v.created_at + '</td><td>' + v.updated_at + '</td> <td><button class="btn btn-sm btn-success" onclick="ShowDetail(' + v.id + ')">内容</button><button class="btn btn-sm btn-info" onclick="UpdateDetail(' + v.id + ')">编辑</button><button class="btn btn-sm btn-danger" onclick="DeleteDetail(' + v.id + ')">删除</button></td></tr>';
            });
            $(dlistsModal).find('tbody').html(tr);

            var title = $('#book_' + id + ' td').eq(2).html();
            $(dlistsModal).find('.modal-title').html(title);

            var paginate = '<ul class="pagination">';
            paginate += '<li><a onclick="getDetailLists('+ id +',1);" rel="prev">首页</a></li>';

            if( res.last_page > 5 ){

                var start = res.current_page;
                if(res.current_page >= 5){
                    start = res.current_page - 4;
                }else{
                    start = 1;
                }

                var end = 6;
                if(res.last_page - res.current_page > end){
                    end = res.current_page + end;
                }else{
                    end = res.last_page;
                }
                for(var i = start;i<=end;i++){
                    paginate += '<li><a onclick="getDetailLists('+ id +','+ i +');" rel="prev">' + i + '</a></li>';
                }
            }else{
                if( res.current_page > 1 ){
                    paginate += '<li><a onclick="getDetailLists('+ id +','+ (res.current_page - 1) +');" rel="prev">上一页</a></li>';
                }
                if( res.current_page < res.total ){
                    paginate += '<li><a onclick="getDetailLists('+ id +','+ (res.current_page + 1) +');" rel="prev">下一页</a></li>';
                }
            }

            paginate += '<li><a onclick="getDetailLists('+ id +','+ res.last_page +');" rel="prev">尾页</a></li>';
            paginate += '</ul>';
            $(dlistsModal).find('tfoot td').html(paginate);

            $(dlistsModal).modal('show');
        }else{
            layer.alert('未找到数据');
        }
    }

    function getDetailLists(id,page){
        loading();
        if(parseInt(page) == 0) page = 1;
        $.ajax({
            url:"{!! route('Book.getDetailLists') !!}",
            data:{id:id,page:page},
            dataType:'json',
            success:function(res){
                showData(id,res);
                loading(true);
            },error:function(res){
                showData(id,res);
                loading(true);
            }
        });
    }
</script>

{{--delete--}}
@include('admin.modal.delete' , ['formurl' => route('Book.getDelete')])

{{--update--}}
<div class="modal inmodal" id="updateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated flipInX">
            <form action="{{ route('Book.postUpdate') }}" method="POST" class="form-horizontal">
                {!! csrf_field() !!}
                <input type="hidden" name="id" value="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">编辑</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">分类</label>
                        <div class="col-sm-10">
                            <select name="catid" id="" class="form-control">
                                <option value="0">请选择</option>
                                @foreach($categorys as $v)
                                    <option value="{{ $v['id'] }}">{{ $v['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">标题</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="title" value="" placeholder="皮皮虾快走">
                            <span class="help-block m-b-none">用来显示的名称</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">简介</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" name="introduce" id="" cols="30" rows="10"placeholder="皮皮虾我们走"></textarea>
                            <span class="help-block m-b-none"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">最新章节</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="zhangjie" value="" placeholder="皮皮虾已经走了">
                            <span class="help-block m-b-none"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">作者</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="author" value="" placeholder="fa-setting">
                            <span class="help-block m-b-none"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
                    <button type="submit" class="btn btn-primary">确定</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{--detail--}}
<div class="modal " id="DetailListsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated flipInX">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">章节列表</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-hover bg-white text-center">
                    <thead>
                    <tr>
                        <td width="50">编号</td>
                        <td width="150" align="left">标题</td>
                        <td>点击量</td>
                        <td>添加时间</td>
                        <td>更新时间</td>
                        <td width="180">操作</td>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">

                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
                <button type="submit" class="btn btn-primary">确定</button>
            </div>
        </div>
    </div>
</div>
@endsection('content')