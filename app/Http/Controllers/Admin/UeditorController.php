<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\DAO\UploaderDAO;

class UeditorController extends Controller
{

    private function getConfig()
    {
        $config = [
            "imageActionName" => "uploadimage",
            "imageFieldName" => "upfile",
            "imageMaxSize" => 2048000,
            "imageAllowFiles" => [
                ".png",
                ".jpg",
                ".jpeg",
                ".gif",
                ".bmp"
            ],
            "imageCompressEnable" => true,
            "imageCompressBorder" => 1600,
            "imageInsertAlign" => "none",
            "imageUrlPrefix" => "",
            "imagePathFormat" => "/upload/ueditor/image/{yyyy}{mm}{dd}/{time}{rand =>6}",
            "scrawlActionName" => "uploadscrawl",
            "scrawlFieldName" => "upfile",
            "scrawlPathFormat" => "/upload/ueditor/image/{yyyy}{mm}{dd}/{time}{rand =>6}",
            "scrawlMaxSize" => 2048000,
            "scrawlUrlPrefix" => "",
            "scrawlInsertAlign" => "none",
            "snapscreenActionName" => "uploadimage",
            "snapscreenPathFormat" => "/upload/ueditor/image/{yyyy}{mm}{dd}/{time}{rand =>6}",
            "snapscreenUrlPrefix" => "",
            "snapscreenInsertAlign" => "none",
            "catcherLocalDomain" => [
                "127.0.0.1",
                "localhost",
                "img.tradingview.com"
            ],
            "catcherActionName" => "catchimage",
            "catcherFieldName" => "source",
            "catcherPathFormat" => "/upload/ueditor/image/{yyyy}{mm}{dd}/{time}{rand =>6}",
            "catcherUrlPrefix" => "",
            "catcherMaxSize" => 2048000,
            "catcherAllowFiles" => [
                ".png",
                ".jpg",
                ".jpeg",
                ".gif",
                ".bmp"
            ],
            "videoActionName" => "uploadvideo",
            "videoFieldName" => "upfile",
            "videoPathFormat" => "/upload/ueditor/video/{yyyy}{mm}{dd}/{time}{rand =>6}",
            "videoUrlPrefix" => "",
            "videoMaxSize" => 102400000,
            "videoAllowFiles" => [
                ".flv",
                ".swf",
                ".mkv",
                ".avi",
                ".rm",
                ".rmvb",
                ".mpeg",
                ".mpg",
                ".ogg",
                ".ogv",
                ".mov",
                ".wmv",
                ".mp4",
                ".webm",
                ".mp3",
                ".wav",
                ".mid"
            ],
            "fileActionName" => "uploadfile",
            "fileFieldName" => "upfile",
            "filePathFormat" => "/upload/ueditor/file/{yyyy}{mm}{dd}/{time}{rand =>6}",
            "fileUrlPrefix" => "",
            "fileMaxSize" => 51200000,
            "fileAllowFiles" => [
                ".png",
                ".jpg",
                ".jpeg",
                ".gif",
                ".bmp",
                ".flv",
                ".swf",
                ".mkv",
                ".avi",
                ".rm",
                ".rmvb",
                ".mpeg",
                ".mpg",
                ".ogg",
                ".ogv",
                ".mov",
                ".wmv",
                ".mp4",
                ".webm",
                ".mp3",
                ".wav",
                ".mid",
                ".rar",
                ".zip",
                ".tar",
                ".gz",
                ".7z",
                ".bz2",
                ".cab",
                ".iso",
                ".doc",
                ".docx",
                ".xls",
                ".xlsx",
                ".ppt",
                ".pptx",
                ".pdf",
                ".txt",
                ".md",
                ".xml"
            ],
            "imageManagerActionName" => "listimage",
            "imageManagerListPath" => "/upload/ueditor/image/",
            "imageManagerListSize" => 20,
            "imageManagerUrlPrefix" => "",
            "imageManagerInsertAlign" => "none",
            "imageManagerAllowFiles" => [
                ".png",
                ".jpg",
                ".jpeg",
                ".gif",
                ".bmp"
            ],
            "fileManagerActionName" => "listfile",
            "fileManagerListPath" => "/upload/ueditor/file/",
            "fileManagerUrlPrefix" => "",
            "fileManagerListSize" => 20,
            "fileManagerAllowFiles" => [
                ".png",
                ".jpg",
                ".jpeg",
                ".gif",
                ".bmp",
                ".flv",
                ".swf",
                ".mkv",
                ".avi",
                ".rm",
                ".rmvb",
                ".mpeg",
                ".mpg",
                ".ogg",
                ".ogv",
                ".mov",
                ".wmv",
                ".mp4",
                ".webm",
                ".mp3",
                ".wav",
                ".mid",
                ".rar",
                ".zip",
                ".tar",
                ".gz",
                ".7z",
                ".bz2",
                ".cab",
                ".iso",
                ".doc",
                ".docx",
                ".xls",
                ".xlsx",
                ".ppt",
                ".pptx",
                ".pdf",
                ".txt",
                ".md",
                ".xml"
            ]
        ];
        return $config;
    }

    public function ueditor(Request $request)
    {
        $action = $request->input('action', '');
        $config = $this->getConfig();
        switch ($action) {
            case 'config':
                return $this->getConfig();
                /* ???????????? */
            case 'uploadimage':
                /* ???????????? */
            case 'uploadscrawl':
                /* ???????????? */
            case 'uploadvideo':
                /* ???????????? */
            case 'uploadfile':
                $field_name = str_ireplace('upload', '', $action) . 'FieldName';
                $field = $config[$field_name];
                $file = $request->file($field);
                $result = UploaderDAO::fileUpload($file, 'ueditor');
                break;
                /* ???????????? */
            case 'listimage':
                //$result = include("action_list.php");
                break;
                /* ???????????? */
            case 'listfile':
                //$result = include("action_list.php");
                break;

                /* ?????????????????? */
            case 'catchimage':
                //$result = include("action_crawler.php");
                break;

            default:
                $result = array(
                    'state' => '??????????????????'
                );
                break;
        }
        return response()->json($result);

    }
}