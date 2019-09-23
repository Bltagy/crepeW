<?php

namespace App\Http\Controllers\API;

use App\Area;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Product;
use App\HomeData;
use App\ProductCategory;
use App\ProductsAddition;
use App\ProductsAdditionCategory;
use App\ProductsAdditionsRelations;
use Illuminate\Http\Request;
use DB;
class SyncController extends BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$input = $request->all();

		$this->categories();

		$this->products();

		$this->addions();

		$this->productsAddions();

		$this->areas();
		HomeData::where('name', 'latest_sync')->update(['value' => \Carbon\Carbon::now()]);
		return $this->sendResponse([], 'Synchronization process has been done successfully.');
	}

	public function categories() {
		$pos_categories = DB::connection('mysql2')->table('Categories')->get();
		$posCatArray    = [];
		foreach ($pos_categories as $pos) {
			$posCatArray[$pos->CategoryID] = $pos;
		}

		$sys_categories = ProductCategory::all();
		$catSysIds      = [];
		foreach ($sys_categories as $value) {
			$catSysIds[] = $value->pos_id;
			if (!in_array($value->pos_id, array_keys($posCatArray))) {
				$product = ProductCategory::where('id', $value->id)->delete();
			}
			if (!empty($value->pos_id) && isset($posCatArray[$value->pos_id])) {
				if ($value->pos_id == $posCatArray[$value->pos_id]->CategoryID) {
					$value->name    = $posCatArray[$value->pos_id]->EnglishCategoryName;
					$value->name_ar = $posCatArray[$value->pos_id]->ArabicCategoryName;
					$value->save();
				}
			}

		}
		foreach ($posCatArray as $posC) {
			if (!in_array($posC->CategoryID, $catSysIds)) {
				$sys_cat          = new ProductCategory;
				$sys_cat->name    = $posC->EnglishCategoryName;
				$sys_cat->name_ar = $posC->ArabicCategoryName;
				$sys_cat->pos_id  = $posC->CategoryID;
				$sys_cat->save();
			}
		}
	}

	public function products() {
		$pos_products = DB::connection('mysql2')->table('Products')->get();
		$posProductsArray = [];
		foreach ($pos_products as $pos) {
			$posProductsArray[$pos->ProductID] = $pos;
		}
		$sys_products  = Product::all();
		$productSysIds = [];
		foreach ($sys_products as $value) {
			$productSysIds[] = $value->pos_id;
			if (!in_array($value->pos_id, array_keys($posProductsArray))) {
				$product = Product::where('id', $value->id)->delete();
			}
			if (!empty($value->pos_id)) {
				if ( isset($posProductsArray[$value->pos_id]) && $value->pos_id == $posProductsArray[$value->pos_id]->ProductID) {
					$value->name                = $posProductsArray[$value->pos_id]->EnglishProductName;
					$value->name_ar             = $posProductsArray[$value->pos_id]->ArabicProductName;
					$value->description         = $posProductsArray[$value->pos_id]->EnglishDescription;
					$value->description_ar      = $posProductsArray[$value->pos_id]->ArabicDescription;
					$value->medium_size         = $posProductsArray[$value->pos_id]->SalesPrice;
					$value->product_category_id = ProductCategory::where('pos_id', $posProductsArray[$value->pos_id]->CategoryID)->first()->id;
					$value->save();
				}
			}

		}

		foreach ($posProductsArray as $posP) {
			if (!in_array($posP->ProductID, $productSysIds)) {
				$sysProdcut                      = new Product;
				$sysProdcut->name                = $posP->EnglishProductName;
				$sysProdcut->name_ar             = $posP->ArabicProductName;
				$sysProdcut->description         = $posP->EnglishDescription;
				$sysProdcut->description_ar      = $posP->ArabicDescription;
				$sysProdcut->pos_id              = $posP->ProductID;
				$sysProdcut->medium_size         = $posP->SalesPrice;
				$sysProdcut->product_category_id = ProductCategory::where('pos_id', $posP->CategoryID)->first()->id;
				$sysProdcut->save();
			}
		}
	}

	public function addions() {
		$pos_products = DB::connection('mysql2')->table('Extras')->get();

		$posAddionsAll = [];
		foreach ($pos_products as $pos) {

			$posAddionsAll[$pos->ExtraID] = $pos;
		}

		$posAddionsCatsIds = [];
		foreach ($posAddionsAll as $posC) {
			if ($posC->ParentItemID == 0) {
				continue;
			}
			$posAddionsCatsIds[] = $posC->ParentItemID;
		}
		$posAddionsCatsIds = array_unique($posAddionsCatsIds);
		

		/**
		 * Categories
		 */

		$sys_additions_categories = ProductsAdditionCategory::all();
		$additionsCatSysIds       = [];
		foreach ($sys_additions_categories as $valueCat) {
			$additionsCatSysIds[] = $valueCat->pos_id;
			if (!in_array($valueCat->pos_id, $posAddionsCatsIds)) {
				$product = ProductsAdditionCategory::where('id', $valueCat->id)->delete();
			}
			if (!empty($valueCat->pos_id)) {
				if ($valueCat->pos_id == $posAddionsAll[$valueCat->pos_id]->ExtraID) {
					$valueCat->name    = $posAddionsAll[$valueCat->pos_id]->EnglishExtraName;
					$valueCat->name_ar = $posAddionsAll[$valueCat->pos_id]->ArabicExtraName;
					$valueCat->save();
				}
			}

		}
		foreach ($posAddionsCatsIds as $posP) {
			if (empty($posAddionsAll[$posP])) {
				continue;
			}

			$posP = $posAddionsAll[$posP];
			if (!in_array($posP->ExtraID, $additionsCatSysIds)) {
				$productsAdditionsCat          = new ProductsAdditionCategory;
				$productsAdditionsCat->name    = $posP->EnglishExtraName;
				$productsAdditionsCat->name_ar = $posP->ArabicExtraName;
				$productsAdditionsCat->pos_id  = $posP->ExtraID;
				$productsAdditionsCat->save();
			}
		}

		/**
		 * End
		 */

		/**
		 * Additions
		 */
		$posAddionsIds = [];
		foreach ($posAddionsAll as $posC) {
			if ($posC->ParentItemID == 0) {
				continue;
			}
			$posAddionsIds[] = $posC->ExtraID;
		}

		$additionsCats = ProductsAdditionCategory::pluck('id', 'pos_id')->toArray();

		$sys_additions   = ProductsAddition::all();
		$additionsSysIds = [];
		foreach ($sys_additions as $valueAdd) {
			$additionsSysIds[] = $valueAdd->pos_id;
			if (!in_array($valueAdd->pos_id, $posAddionsIds)) {
				$product = ProductsAddition::where('id', $valueAdd->id)->delete();
			}
			if (!empty($valueAdd->pos_id)) {
				if ($valueAdd->pos_id == $posAddionsAll[$valueAdd->pos_id]->ExtraID) {
					$valueAdd->name    = $posAddionsAll[$valueAdd->pos_id]->EnglishExtraName;
					$valueAdd->name_ar = $posAddionsAll[$valueAdd->pos_id]->ArabicExtraName;
					$valueAdd->price   = $posAddionsAll[$valueAdd->pos_id]->SalesPrice;
					if ($posAddionsAll[$valueAdd->pos_id]->ParentItemID) {
						$valueAdd->parent_id = $posAddionsAll[$valueAdd->pos_id]->ParentItemID;
						if (!empty($additionsCats[$posAddionsAll[$valueAdd->pos_id]->ParentItemID])) {
							$valueAdd->addition_category_id = $additionsCats[$posAddionsAll[$valueAdd->pos_id]->ParentItemID];
						}
					}
					$valueAdd->save();
				}
			}

		}

		foreach ($posAddionsIds as $posP) {
			$posP = $posAddionsAll[$posP];
			if (!in_array($posP->ExtraID, $additionsSysIds)) {
				$productsAdditions          = new ProductsAddition;
				$productsAdditions->name    = $posP->EnglishExtraName;
				$productsAdditions->name_ar = $posP->ArabicExtraName;
				$productsAdditions->pos_id  = $posP->ExtraID;
				$productsAdditions->price   = $posP->SalesPrice;
				if ($posP->ParentItemID) {
					$productsAdditions->parent_id = $posP->ParentItemID;
					if (!empty($additionsCats[$posP->ParentItemID])) {
						$productsAdditions->addition_category_id = $additionsCats[$posP->ParentItemID];
					}
				}
				$productsAdditions->save();
			}
		}

		/**
		 * End
		 */
	}

	public function productsAddions() {
		$pos_products = DB::connection('mysql2')->table('SalesItemExtras')->get();
		ProductsAdditionsRelations::truncate();
		$posAddionsAll = [];
		foreach ($pos_products as $pos) {
			$posAddionsAll[$pos->SalesItemID][] = $pos->ExtraID;
		}
		foreach ($posAddionsAll as $key => $value) {
			$product = Product::where('pos_id', $key)->first();
			$addions = ProductsAdditionCategory::whereIn('pos_id', $value)->pluck('id')->toArray();
			$product->addions()->sync($addions);
		}
	}

	public function areas() {
		$pos_areas = DB::connection('mysql2')->table('Zones')->get();

		$posAreaArray = [];
		foreach ($pos_areas as $pos) {
			$posAreaArray[$pos->ZoneID] = $pos;
		}
		$sys_areas = Area::all();
		$catSysIds = [];
		foreach ($sys_areas as $value) {
			$catSysIds[] = $value->pos_id;
			if (!in_array($value->pos_id, array_keys($posAreaArray))) {
				$product = Area::where('id', $value->id)->delete();
			}
			if (!empty($value->pos_id) && isset($posAreaArray[$value->pos_id])) {
				if ($value->pos_id == $posAreaArray[$value->pos_id]->ZoneID) {
					$value->name               = $posAreaArray[$value->pos_id]->EnglishZoneName;
					$value->name_ar            = $posAreaArray[$value->pos_id]->ArabicZoneName;
					$value->fees               = $posAreaArray[$value->pos_id]->DeliveryFees;
					$value->destination_branch = $posAreaArray[$value->pos_id]->DestinationBranchName;
					$value->save();
				}
			}

		}
		foreach ($posAreaArray as $posC) {
			if (!in_array($posC->ZoneID, $catSysIds)) {
				$sys_area                     = new Area;
				$sys_area->name               = $posC->EnglishZoneName;
				$sys_area->name_ar            = $posC->ArabicZoneName;
				$sys_area->pos_id             = $posC->ZoneID;
				$sys_area->fees               = $posC->DeliveryFees;
				$sys_area->destination_branch = $posC->DestinationBranchName;
				$sys_area->save();
			}
		}
	}

}