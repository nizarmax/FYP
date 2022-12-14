<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\file;
use App\Models\studentFile;
use App\Models\courseFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\program;
use Illuminate\Support\Facades\DB;

class fileController extends Controller
{
    public function index()
    {
        $currentUserProgramDetailsID = Auth::user()->programDetailsID;
        $currentProgramCourseListID = DB::table('program_details')->where('programDetailsID',$currentUserProgramDetailsID)->value('courseListID');
        $listOfCoursesForCourseListID = DB::table('course_lists')->where('courseListID',$currentProgramCourseListID)->get();
        $files = File::all();

        return view('students/main', [
            'documents' => $files,
            'courseList' => $listOfCoursesForCourseListID,
        ]);
    }

    public function staffindex()
    {
        $currentCourseList = Auth::user()->courseListID;
        $listOfCoursesForCourseListID = DB::table('course_lists')->where('courseListID',$currentCourseList)->get();
        $files = File::all();

        return view('staff/staff_main', [
            'documents' => $files,
            'courseList' => $listOfCoursesForCourseListID,
        ]);
    }

    public function show()
    {
        $files = File::all();

        return view('students/main', [
            'documents' => $files,
        ]);
    }

    public function destroy($id)
    {
        $doc = File::where('fileID', $id)->delete();
        

        return redirect()->route('document');
    }

    public function update(Request $request)
    {
        $request->validate([
            'file' => 'mimes:jpeg,bmp,png,pdf,jpg,docx,txt',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->storeAs('PDF', $_FILES['file']['name']);
            $realPath = storage_path()."\\app\\".str_replace('/','\\',$path);
            $pdf = new Pdf($realPath);
            $num = $pdf->getNumberOfPages();
            $imageData = $pdf->saveImage('image');
            $base64Img = base64_encode($imageData);
            $ext = $request->file->extension();
            $doc = file_get_contents($realPath);
            $base64 = base64_encode($doc);
            $mime = $request->file('file')->getClientMimeType();

            $createFile = File::create([
                'fileName' => $_FILES['file']['name'],
                'fileType' => $ext,
                'mime' => $mime,
                'noPage' => $num,   
                'file' => $base64,
                'thumbnail' => $base64Img,
            ]);

            $fileID = $createFile->fileID;
            $studentID =  $request->input('studentID');

            StudentFile::create([
                'fileID' => $fileID,
                'studentID' => $studentID,
            ]);
            
            return redirect()->route('document');
        }
    }

    public function staffupdate(Request $request)
    {
        $request->validate([
            'file' => 'mimes:jpeg,bmp,png,pdf,jpg,docx,txt',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->storeAs('PDF', $_FILES['file']['name']);
            $realPath = storage_path()."\\app\\".str_replace('/','\\',$path);
            $pdf = new Pdf($realPath);
            $num = $pdf->getNumberOfPages();
            $imageData = $pdf->saveImage('image');
            $base64Img = base64_encode($imageData);
            $ext = $request->file->extension();
            $doc = file_get_contents($realPath);
            $base64 = base64_encode($doc);
            $mime = $request->file('file')->getClientMimeType();

            $createFile = File::create([
                'fileName' => $_FILES['file']['name'],
                'fileType' => $ext,
                'mime' => $mime,
                'noPage' => $num,   
                'file' => $base64,
                'thumbnail' => $base64Img,
            ]);

            $fileID = $createFile->fileID;
            $courseID =  $request->input('courseID');

            CourseFile::create([
                'fileID' => $fileID,
                'courseID' => $courseID,
            ]);

            //unlink(storage_path('app/PDF/'.$_FILES['file']['name']));

            return redirect()->route('staffMainPage');
        }
    }

    public function download($id)
    {
        $document = File::where('fileID', $id)->first();

        $file_contents = base64_decode($document->file);

        return response($file_contents)
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', $document->mime)
            ->header('Content-length', strlen($file_contents))
            ->header('Content-Disposition', 'attachment; filename=' . $document->fileName)
            ->header('Content-Transfer-Encoding', 'binary');
    }
}
