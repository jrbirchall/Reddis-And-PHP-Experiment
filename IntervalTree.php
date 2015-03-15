<?php
require_once __DIR__ . '/IntervalTreeNode.php';
require_once __DIR__ . '/Range.php';

class IntervalTree
{
    protected $_tree = null;
    protected $id = null;
    public function __construct()
    {
        $this->id = uniqid();
        $this->_tree = array();
    }

    public function addRange( $range, $i=0 )
    {
        if (FALSE == isset($this->_tree[$i]))
        {
            $node = new IntervalTreeNode($range);
            $this->_tree[] = $node;
            return;
        }

        $node = $this->_tree[$i];
        $cmp = $node->range->cmp($range);
        switch ($cmp)
        {
            case Range::PARTIAL_OVERLAP_LOWER:
                $range->upper = $node->range->lower;
            case Range::DISJOINT_LOWER:
                if (null == $node->left)
                {
                    $node->left = count($this->_tree);
                }
                $this->addRange($range, $node->left);
                break;
            case Range::PARTIAL_OVERLAP_UPPER:
                $range->lower = $node->range->upper;
            case Range::DISJOINT_UPPER:
                if (null == $node->right)
                {
                    $node->right = count( $this->_tree );
                }
                $this->addRange($range, $node->right);
                break;
        }
    }

    public function height( $i=0 )
    {
        if (FALSE == isset($this->_tree[$i]))
            return 0;

        $node = $this->_tree[$i];
        $height_left = $this->height($node->left);
        $height_right = $this->height($node->right);
        return ($height_left > $height_right) ? ++$height_left : ++$height_right;
    }

    public function queryRange( $range, $i = 0 )
    {
        if (FALSE == isset($this->_tree[$i]))
            return FALSE;

        $node = $this->_tree[$i];
        $cmp = $node->range->cmp($range);
        switch ($cmp)
        {
            case Range::CONTAINS_FULLY:
                return TRUE;
            case Range::PARTIAL_OVERLAP_LOWER:
                $range->upper = $node->range->lower;
            case Range::DISJOINT_LOWER:
                return $this->queryRange($range, $node->left);
            case Range::PARTIAL_OVERLAP_UPPER:
                $range->lower = $node->range->upper;
            case Range::DISJOINT_UPPER:
                return $this->queryRange($range, $node->right);
        }
        return FALSE;
    }

    public function removeRange( $range, $i=0 )
    {
        if (FALSE == isset($this->_tree[$i]))
            return;

        $node = $this->_tree[$i];
        $cmp = $node->range->cmp( $range );
        switch ($cmp)
        {
            case Range::DISJOINT_LOWER:
                return $this->removeRange( $range, $node->left );
            case Range::DISJOINT_UPPER:
                return $this->removeRange( $node->upper );
            case Range::PARTIAL_OVERLAP_LOWER:
                $overlap_range = new Range($node->lower, $range->upper);
                $range->upper = $node->range->lower;
                $this->splitNode( $node, $overlap_range );
                return $this->removeRange($range, $node->left);
            case Range::PARTIAL_OVERLAP_UPPER:
                $overlap_range = new Range($range->lower, $node->range->upper);
                $range->lower = $node->range->upper;
                $this->splitNode($node, $overlap_range);
                return $this->removeRange($range, $node->right);
            case Range::CONTAINS_FULLY:
                return $this->splitNode($node, $range);
        }
    }

    protected function splitNode( $node, $range )
    {
        $new_range = new Range($range->lower, $node->range->lower);
        $new_node = new IntervalTreeNode($new_range);
        $new_node->left = $node->left;

        $node->range->lower = $range->upper;
        $node->left = count($this->_tree);
        $this->_tree[] = $new_node;
    }
}
?>
