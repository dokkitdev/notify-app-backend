<?php

namespace App\Http\Controllers\Template;

use App\Appointment;
use App\Http\Controllers\Controller;
use App\Service\JobUploader;
use App\Service\simProRequestService;
use App\TemplateParent;
use App\Templates;
use CloudConvert\Api;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Psr\Http\Message\ResponseInterface;


class TemplateController extends Controller
{

    /** Главная страница всех темплейтов */
    public function all()
    {
        $templateParents = TemplateParent::all();
        return view('admin.templates.new.index', [
            'templateParents' => $templateParents,
        ]);
    }

    /** Редактирование темплейта(метод GET) */
    public function getTemplate($id)
    {
        $template = Templates::find($id);
        if ($template) {
            return view('admin.templates.new.edit', [
                'template' => $template
            ]);
        }
        return redirect()->route('templates.all');
    }

    /** Редактирование темплейта(метод PUT) */
    public function putTemplate(Request $request, $id)
    {
        $template = Templates::find($id);
        $data = $request->all();
        if ($template) {
            $template->subject = $data['subject'] ?? $template->subject;
            $template->html_body = $data['html_body'] ?? $template->html_body;

            if (isset($data['file']) && $data['file']) {
                $html = md5(uniqid('template', true)) . '.html';
                $docx = md5(uniqid('template', true)) . '.docx';

                $html_folder = Config::get('constants.storage_html');
                $docx_folder = Config::get('constants.storage_docx') . '/';
                $pdf_folder = Config::get('constants.storage_pdf');

                $data['file']->move($docx_folder, $docx);
                $template->docx = $docx;

                $docx_file_path = $docx_folder . $docx;

                exec('libreoffice --headless --writer --convert-to pdf ' . $docx_file_path . ' --outdir ' . $pdf_folder);
                $pdf = substr($docx, 0, -4) . 'pdf';
                $template->pdf = $pdf;
            }
            $template->save();
        }
        return redirect()->route('templates.all');
    }

    /** AJAX обновление docx */
    public function uploadDocx(Request $request)
    {
        $req = $request->all();
        if (!array_key_exists('file', $req) ||
            !array_key_exists('alias', $req)
        ) {
            return false;
        }

        $template = Templates::where('alias', '=', $req['alias'])->first();

        if (!$template) {
            return false;
        }

        /** Получение папок */
        $pdf_folder = Config::get('constants.storage_pdf');
        $docx_folder = Config::get('constants.storage_docx') . '/';

        $docx = md5(uniqid('template', true)) . '.docx';
        $docx_file_path = $docx_folder . $docx;

        /** Создаем файл на сервере и конвертируем его в пдф */
        $req['file']->move($docx_folder, $docx);
        exec('libreoffice --headless --writer --convert-to pdf ' . $docx_file_path . ' --outdir ' . $pdf_folder);
        $pdf = substr($docx, 0, -4) . 'pdf';
        $template->pdf = $pdf;
        $template->docx = $docx;
        $template->save();

        return response()->json(Config::get('constants.storage_docx') . '/' .$template->docx);
    }


}


