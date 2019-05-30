<?php
class Zend_View_Helper_Paginator extends Zend_View_Helper_Partial
{
	public function Pages($obj){

        $text = '';



        if ($obj->pageCount) {

            $text .= '<div class="paginationControl">Displaying(' . $obj->firstItemNumber . '-' . $obj->lastItemNumber . ')

				  of ' . $obj->totalItemCount;

        }

        //First page link /

        if (isset($obj->previous)) {

            $text .= '  <a href="' . '?page='.$obj->first . '"  style="text-decoration:none"> &lt;&lt; First </a> |';

        } else {

            $text .= '<span class="disabled"> &lt;&lt;First</span> |';

        }

        //Previous page link

        if (isset($obj->previous)) {

            $text .= '<a href="' . '?page='.$obj->previous . '" style="text-decoration:none">&lt; Previous</a> |';

        } else {

            $text .= '<span class="disabled">&lt; Previous</span>|';

        }

		//Numbered page links

		if(is_array($obj->pagesInRange)){

		   foreach ($obj->pagesInRange as $page){

              if ($page != $obj->current){

                 $text .= '<a href="'.'?page='. $page.'">';

                 $text .= $page.'</a> |';

              }else{

                  $text .= $page.' |';

              }

          }

	  }

		

        //Next page link

        if (isset($obj->next)) {

            $text .= '<a href="' . '?page='. $obj->next. '" style="text-decoration:none">Next &gt;</a> |';

        } else {

            $text .= '<span class="disabled">Next &gt;</span> <span>|</span>';

        }

        //Last page link

        if (isset($obj->next)) {

            $text .= '<a href="' . '?page='. $obj->last. '" style="text-decoration:none"> Last &gt;&gt;</a>';

        } else {

            $text .= '<span class="disabled">Last&gt;&gt;</span>';

        }

        $text .='</div>';



        return $text;

    }

}

?>