<?php

namespace AscentCreative\BibleRef\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BibleRef extends Model
{

    use HasFactory;

    protected $table = 'bible_refs';
    protected $fillable = ['biblerefable_type', 'biblerefable_id', 'biblerefable_key', 'biblerefable_sort', 'ref', 'book_id', 'start_chapter', 'start_verse', 'start_verse_suffix', 'end_chapter', 'end_verse', 'end_verse_suffix', 'ref', 'start_key', 'end_key'];

    protected $appends = ['reference', 'abbreviated'];
    // protected $visible = ['reference'];
    protected $hidden = ['id', 'biblerefable_type', 'biblerefable_id', 'biblerefable_key', 'biblerefable_sort', 'created_at', 'updated_at'];

    protected static function booted() {

        static::addGlobalScope('default_sort', function($q) {
            $q  ->orderBy('book_id')
                ->orderBy('start_chapter')
                ->orderBy('start_verse')
                ->orderBy('end_chapter')
                ->orderBy('end_verse');
        });

        static::saving(function($model) {

            $sc = str_pad ( $model->start_chapter , 3 , "0", STR_PAD_LEFT ); 
            $sv = str_pad ( $model->start_verse , 3 , "0", STR_PAD_LEFT );
            $ec = str_pad ( $model->end_chapter , 3 , "0", STR_PAD_LEFT ); 
            $ev = str_pad ( $model->end_verse , 3 , "0", STR_PAD_LEFT );

            $model->start_key = $sc . ":" . $sv;
            $model->end_key = $ec . ":" . $ev;

        });

    }



    public function __toString() {
        $brp = new \AscentCreative\BibleRef\Parser();
        return $brp->makeBibleRefFromArray($this->toArray());
    }

    public function getReferenceAttribute() {
        // return '123';
        $brp = new \AscentCreative\BibleRef\Parser();

        // can't use toArray() as that causes a loop due to be called in "appends"
        return $brp->makeBibleRefFromArray([
            'book_id'=>$this->book_id,
            'start_chapter'=>$this->start_chapter,
            'start_verse'=>$this->start_verse,
            'start_verse_suffix'=>$this->start_verse_suffix,
            'end_chapter'=>$this->end_chapter,
            'end_verse'=>$this->end_verse,
            'end_verse_suffix'=>$this->end_verse_suffix,
        ], false);
    }

    public function getAbbreviatedAttribute() {
        // return '123';
        $brp = new \AscentCreative\BibleRef\Parser();

        // can't use toArray() as that causes a loop due to be called in "appends"
        return $brp->makeBibleRefFromArray([
            'book_id'=>$this->book_id,
            'start_chapter'=>$this->start_chapter,
            'start_verse'=>$this->start_verse,
            'start_verse_suffix'=>$this->start_verse_suffix,
            'end_chapter'=>$this->end_chapter,
            'end_verse'=>$this->end_verse,
            'end_verse_suffix'=>$this->end_verse_suffix,
        ]);
    }


}
 