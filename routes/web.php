<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RawMaterialCategoryController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierProductController;
use App\Http\Controllers\PODetailController;
use App\Http\Controllers\POProductDetailController;
use App\Http\Controllers\PoCorrectionController;
use App\Http\Controllers\RackmasterController;
use App\Http\Controllers\RackStockmasterController;
use App\Http\Controllers\GRNInwardRegisterController;
use App\Http\Controllers\GrnQualityController;
use App\Http\Controllers\HeatNumberController;
use App\Http\Controllers\ItemProcesmasterController;
use App\Http\Controllers\StagewiseReceiveController;
use App\Http\Controllers\StagewiseIssueController;
use App\Http\Controllers\GrnRejectionController;
use App\Http\Controllers\ProductProcessMasterController;
use App\Http\Controllers\DcMasterController;
use App\Http\Controllers\CustomerMasterController;
use App\Http\Controllers\CustomerPoMasterController;
use App\Http\Controllers\CustomerProductMasterController;
use App\Http\Controllers\FinalQcInspectionController;
use App\Http\Controllers\DcTransactionDetailsController;
use App\Http\Controllers\DcPrintController;
use App\Http\Controllers\InvoiceDetailsController;
use App\Http\Controllers\InvoiceCorrectionMasterController;
use App\Http\Controllers\InvoiceCorrectionDetailController;
use App\Http\Controllers\StageQrCodeLockController;
use App\Http\Controllers\RetrunRMDetailsController;
use App\Http\Controllers\SalesDespatchPlanSummaryController;
use App\Http\Controllers\SalesDespatchPlanTransactionController;
use App\Http\Controllers\RMDcController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RmRequistionController;
use App\Http\Controllers\PermissionController;
use App\Models\InvoiceDetails;
use Spatie\Permission\Contracts\Role;
use App\Http\Controllers\DCReportController;
use App\Http\Controllers\TransDataD12Controller;

// invoice updated

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();
Route::group(['middleware' => ['auth','role:Super Admin']], function () {
    Route::get('role_permission/{role_id}',[RoleController::class,'role_permission'])->name('role_permission');
    Route::post('assign_permission',[RoleController::class,'assign_permissions'])->name('assign_permission');
    Route::resource('roles',RoleController::class);
    Route::resource('permissions',PermissionController::class);
});
Route::middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/customers-data/id', [CustomerMasterController::class,'customersData'])->name('customersdata');
    Route::post('customers-edit', [CustomerMasterController::class,'customersEditData'])->name('customerseditdata');
    Route::get('/suppliers-data/id', [SupplierController::class,'suppliersdata'])->name('suppliersdata');
    Route::get('/rmcategorydata-data/id', [SupplierProductController::class,'rmcategorydata'])->name('rmcategorydata');
    Route::post('add_purchase_item', [PODetailController::class,'addPurchaseItem'])->name('add_purchase_item');
    Route::post('add_grn_item', [GRNInwardRegisterController::class,'addGRNItem'])->name('add_grn_item');
    Route::get('/posuppliers-data/id', [PODetailController::class,'posuppliersdata'])->name('posuppliersdata');
    Route::post('/posuppliersrmdata-data', [PODetailController::class,'posuppliersrmdata'])->name('posuppliersrmdata');
    Route::post('/posuppliersproductdata-data', [PODetailController::class,'posuppliersproductdata'])->name('posuppliersproductdata');
    Route::get('/poprint-data/{id}', [PODetailController::class,'poprint'])->name('po.print');
    Route::get('poprint-url', [PODetailController::class,'poUrl'])->name('po.url');
    Route::get('/pocorrection-data/{id}', [PODetailController::class,'pocorrection'])->name('po.correction');
    Route::get('/pocorrection-approval-data/{id}', [PoCorrectionController::class,'approval'])->name('pocorrection.approval');
    Route::get('/grnsuppliers-fetchdata/id', [PODetailController::class,'grn_supplierfetchdata'])->name('grn_supplierfetchdata');
    Route::get('/grnrm-fetchdata/id', [GRNInwardRegisterController::class,'grn_rmfetchdata'])->name('grn_rmfetchdata');
    Route::get('/grn_iqc-data/{id}', [GrnQualityController::class,'approval'])->name('grn_iqc.approval');
    Route::get('/rm-printdata/{id}', [GRNInwardRegisterController::class,'rmIssuancePrint'])->name('rmissuance.show');
    Route::get('/rm-printdata2/{id}', [GRNInwardRegisterController::class,'rmIssuancePrint2'])->name('rmissuance.show2');
    Route::get('rm_issuance', [GRNInwardRegisterController::class,'rmIssuanceData'])->name('rmissuance.index');
    Route::get('rm_returnreceipt', [GRNInwardRegisterController::class,'rmReturnReceipt'])->name('returnrmreceipt');
    Route::get('rm_return', [GRNInwardRegisterController::class,'rmReturnData'])->name('returnrm.index');
    Route::post('rm_returnstore', [GRNInwardRegisterController::class,'rmReturnStore'])->name('returnrm.store');
    Route::get('workorder-create', [PODetailController::class,'workOrderCreate'])->name('workorder.create');
    Route::get('rm_issuance-create', [GRNInwardRegisterController::class,'rmIssuance'])->name('rmissuance.create');
    Route::post('/rm_issuance-data', [GRNInwardRegisterController::class,'storeData'])->name('rmissuance.storedata');
    Route::post('/rm_issuance-fetchdata', [GRNInwardRegisterController::class,'grnRmFetchData'])->name('grnrmfetchdata');
    Route::post('/rm_heatno-fetchdata', [GRNInwardRegisterController::class,'grnHeatFetchData'])->name('grnheatfetchdata');
    Route::post('/rm_coilno-fetchdata', [GRNInwardRegisterController::class,'grnCoilFetchData'])->name('grncoilfetchdata');
    Route::post('/grnqc-fetchdata', [GRNInwardRegisterController::class,'grnQcFetchData'])->name('grnqcfetchdata');
    Route::get('sf_receive/list', [StagewiseReceiveController::class,'sfReceiveList'])->name('sfreceive');
    Route::get('sf_receive/create', [StagewiseReceiveController::class,'sfReceiveCreateForm'])->name('sfreceive.create');
    Route::post('sf-receive', [StagewiseReceiveController::class,'sfReceiveEntry'])->name('sfreceive.store');
    Route::post('sf-receive/part_no', [StagewiseReceiveController::class,'sfPartFetchEntry'])->name('sfpartfetchdata');
    Route::get('/sfreceive-qrprintdata/{id}', [StagewiseReceiveController::class,'sfPartReceiveQrCode'])->name('sfpartreceiveqrcode');
    Route::get('/ptsclereceive-qrprintdata/{id}', [StagewiseReceiveController::class,'ptsClePartIssueQrCode'])->name('ptsclepartqrcodeissue');
    Route::get('/sfissue-qrprintdata/{id}', [StagewiseIssueController::class,'sfPartIssueQrCode'])->name('sfpartissueqrcode');
    Route::get('sf_issue/list', [StagewiseIssueController::class,'sfIssueList'])->name('sfissue');
    Route::get('sf_issue/create', [StagewiseIssueController::class,'sfIssueCreateForm'])->name('sfissue.create');
    Route::post('sf_issue', [StagewiseIssueController::class,'sfIssueEntry'])->name('sfissue.store');
    Route::post('sf_issue/part_no', [StagewiseIssueController::class,'sfIssuePartFetchEntry'])->name('sfissuepartfetchdata');
    Route::get('os_receive/list', [StagewiseReceiveController::class,'osReceiveList'])->name('osreceive');
    Route::get('os_receive/create', [StagewiseReceiveController::class,'osReceiveCreateForm'])->name('osreceive.create');
    Route::post('os-receive', [StagewiseReceiveController::class,'osReceiveEntry'])->name('osreceive.store');
    Route::post('os-receive/part_no', [StagewiseReceiveController::class,'osPartFetchEntry'])->name('ospartfetchdata');
    Route::get('fg_qrreceive/list', [StagewiseReceiveController::class,'fgReceiveQRList'])->name('fgqrreceive');
    Route::get('fg_qrreceive/create', [StagewiseReceiveController::class,'fgReceiveQRCreateForm'])->name('fgqrreceive.create');
    Route::get('fg_qrdcreceive/create', [StagewiseReceiveController::class,'fgDcReceiveQRCreateForm'])->name('fgqrdcreceive.create');
    Route::post('fg-qrreceive', [StagewiseReceiveController::class,'fgReceiveQREntry'])->name('fgqrreceive.store');
    Route::post('fg-qrreceive/part_no', [StagewiseReceiveController::class,'fgQRPartFetchEntry'])->name('fgqrpartfetchdata');
    Route::get('fg_receive/list', [StagewiseReceiveController::class,'fgReceiveList'])->name('fgreceive');
    Route::get('fg_receive/create', [StagewiseReceiveController::class,'fgReceiveCreateForm'])->name('fgreceive.create');
    Route::post('fg-receive', [StagewiseReceiveController::class,'fgReceiveEntry'])->name('fgreceive.store');
    Route::post('fg-receive/part_no', [StagewiseReceiveController::class,'fgPartFetchEntry'])->name('fgpartfetchdata');
    Route::get('fg-receive/fqc_approval', [StagewiseReceiveController::class,'fgFqcApproval'])->name('fgfqc');
    Route::get('os-receive/fqc_approval', [StagewiseReceiveController::class,'osFqcApproval'])->name('osfqc');
    Route::get('fg-qrreceive/dc-pending', [StagewiseReceiveController::class,'fgQrDC'])->name('fgqrdc');
    Route::post('dcfetch-rc', [DcTransactionDetailsController::class,'dcItemRc'])->name('dcitemrc');
    Route::post('dcfetch-rcqty', [DcTransactionDetailsController::class,'dcItemRcQuantity'])->name('dcitemrcquantity');
    Route::get('/dcpart-data/id', [DcTransactionDetailsController::class,'dcPartData'])->name('dcpartdata');
    Route::get('/dcrmsupplier-data/id', [RMDcController::class,'dcRmSupplierData'])->name('dcrmsupplierdata');
    Route::get('rmdc_receive/create', [RMDcController::class,'rmdcReceiveData'])->name('rmdc_receive');
    Route::post('rmdcreceiverc-data', [RMDcController::class,'rmdcReceiveRcData'])->name('rmdcreceivercdata');
    Route::post('rmdcreceivepart-data', [RMDcController::class,'rmdcReceivePartData'])->name('rmdcreceivepartdata');
    Route::post('rmdcreceive-store', [RMDcController::class,'rmdcReceiveStore'])->name('rmdcreceive.store');
    Route::post('dcrmgrn-data', [RMDcController::class,'dcrmGrnData'])->name('dcrmgrndata');
    Route::post('dcrmgrncoil-data', [RMDcController::class,'dcrmGrnCoilData'])->name('dcrmgrncoildata');
    Route::get('/rmdcmultiprint-create', [RMDcController::class,'rmdcPrintForm'])->name('rmdcmultiprintform');
    Route::post('rmdcsupplier-print', [DcPrintController::class,'rmDcSupplierPrintData'])->name('rmdcsupplierprintdata');
    Route::post('dcrmgrncoilqty-data', [RMDcController::class,'dcrmGrnCoilQtyData'])->name('dcrmgrncoilqtydata');
    Route::post('dcsupplier-print', [DcPrintController::class,'dcSupplierPrintData'])->name('dcsupplierprintdata');
    Route::post('dcmulti-print', [DcPrintController::class,'dcMultiPrintData'])->name('dcmultiprintdata');
    Route::post('dcmulti-pdf', [DcPrintController::class,'dcMultiPdfData'])->name('dcmultipdf');
    Route::post('ptsdcmulti-pdf', [DcPrintController::class,'ptsdcMultiPdfData'])->name('ptsdcmultipdf');
    Route::get('/dcmulti-receive', [DcPrintController::class,'ptsMultiDCReceive'])->name('ptsmultidcreceive');
    Route::get('/ptsdcmulti-receivelist', [DcPrintController::class,'ptsdcMultiList'])->name('ptsmultidcreceivelist');
    Route::get('/ptsdcmulti-receivelist/create', [DcPrintController::class,'ptsdcMultiCreate'])->name('ptsmultidccreate');
    Route::get('/ptsdcmulti-inwardlist', [DcPrintController::class,'ptsInwardData'])->name('ptsinwarddata');
    Route::post('ptsdcmulti-receive', [DcPrintController::class,'ptsdcMultiReceiveData'])->name('ptsdcmultireceivedata');
    Route::get('/dcmulti-handover', [DcPrintController::class,'ptsMultiDCHandOverList'])->name('ptsmultidchandoverlist');
    Route::post('ptsdcmulti-handover', [DcPrintController::class,'ptsMultiDCHandOverData'])->name('ptsmultidchandoverdata');
    Route::post('ptsdcmulti-store', [DcPrintController::class,'ptsMultiDcStore'])->name('ptsdcmultidcstore');
    Route::get('pts_production_issue/list', [StagewiseReceiveController::class,'ptsProductionIssueList'])->name('ptsproductionissue');
    Route::get('pts_production_issue/create', [StagewiseReceiveController::class,'ptsProductionIssueCreateForm'])->name('ptsproductionissue.create');
    Route::post('pts_production-issue', [StagewiseReceiveController::class,'ptsProductionIssueEntry'])->name('ptsproductionissue.store');
    Route::post('pts_production-issue/part_no', [StagewiseReceiveController::class,'ptsProductionIssuePartFetchEntry'])->name('ptsproductionpartissuefetchdata');
    Route::get('pts_production_receive/list', [StagewiseReceiveController::class,'ptsProductionReceiveList'])->name('ptsproductionreceive');
    Route::get('pts_production_receive/create', [StagewiseReceiveController::class,'ptsProductionReceiveCreateForm'])->name('ptsproductionreceive.create');
    Route::get('pts-stock/list', [StagewiseReceiveController::class,'ptsStockList'])->name('ptsstocklist');
    Route::post('pts_production-receive', [StagewiseReceiveController::class,'ptsProductionReceiveEntry'])->name('ptsproductionreceive.store');
    Route::post('pts_production-receive/part_no', [StagewiseReceiveController::class,'ptsProductionReceivePartFetchEntry'])->name('ptsproductionpartreceivefetchdata');
    Route::post('pts_production-receive/rc_no', [StagewiseReceiveController::class,'ptsProductionReceiveRcFetchEntry'])->name('ptsproductionrcreceivefetchdata');
    Route::get('pts_cle_issue/list', [StagewiseReceiveController::class,'ptsCleIssueList'])->name('ptscleissue');
    Route::get('pts_cle_issue/create', [StagewiseReceiveController::class,'ptsCleIssueCreateForm'])->name('ptscleissue.create');
    Route::post('pts_cle-issue', [StagewiseReceiveController::class,'ptsCleIssueEntry'])->name('ptscleissue.store');
    Route::post('pts_cle-receive/part_id', [StagewiseReceiveController::class,'ptsCleReceiveRcFetchEntry'])->name('ptsclercreceivefetchdata');
    Route::post('pts_cle-issue/rc_no', [StagewiseReceiveController::class,'ptsCleIssueRcFetchEntry'])->name('ptsclercissuefetchdata');
    Route::post('pts_cle-issue/part_no', [StagewiseReceiveController::class,'ptsCleIssuePartFetchEntry'])->name('ptsclepartissuefetchdata');
    Route::get('pts_dc_issue/list', [StagewiseReceiveController::class,'ptsDcIssueList'])->name('ptsdcissue');
    Route::get('pts_dc_issue/create', [StagewiseReceiveController::class,'ptsDcIssueCreateForm'])->name('ptsdcissue.create');
    Route::post('pts_dc-issue', [StagewiseReceiveController::class,'ptsDcIssueEntry'])->name('ptsdcissue.store');
    Route::post('ptsdcfetch-rc', [StagewiseReceiveController::class,'ptsdcItemRc'])->name('ptsdcitemrc');
    Route::post('ptsdcfetch-custype', [StagewiseReceiveController::class,'ptsdcCusType'])->name('ptsdccustype');
    Route::post('ptsdcfetch-rcqty', [StagewiseReceiveController::class,'ptsdcItemRcQuantity'])->name('ptsdcitemrcquantity');
    Route::post('pts_dc-issue/part_no', [StagewiseReceiveController::class,'ptsDcIssuePartFetchEntry'])->name('ptsdcpartissuefetchdata');
    Route::post('pts_cle-issue/cover', [StagewiseReceiveController::class,'ptsCleCoverFetchEntry'])->name('ptsclecoverfetchdata');
    Route::post('pts_cle-issue/packing_cover', [StagewiseReceiveController::class,'ptsClePackingCoverFetchEntry'])->name('ptsclepackingcoverdata');
    Route::get('pts_cle-receive/list', [StagewiseReceiveController::class,'ptsCleReceiveList'])->name('ptsclereceive');
    Route::get('pts_cle-receive/create', [StagewiseReceiveController::class,'ptsCleReceiveCreateForm'])->name('ptsclereceive.create');
    Route::post('pts_cle-receive', [StagewiseReceiveController::class,'ptsCleReceiveEntry'])->name('ptsclereceive.store');
    Route::post('pts_cle-receive/part_no', [StagewiseReceiveController::class,'ptsCleReceivePartFetchEntry'])->name('ptsclepartreceivefetchdata');
    Route::get('pts_fqc/list', [FinalQcInspectionController::class,'ptsFqcList'])->name('ptsfqclist');
    Route::get('qc_rejection/create', [StagewiseReceiveController::class,'qcRejection'])->name('qcrejection');
    Route::get('qc_rejection/list', [StagewiseReceiveController::class,'qcRejectionList'])->name('qcrejectionlist');
    Route::post('qc_qrreceive/rc_rej', [StagewiseReceiveController::class,'qcQRRcRejFetchEntry'])->name('qcqrrcrejfetchdata');
    Route::post('qc-qrrejection/store', [StagewiseReceiveController::class,'qcQRRcRejStore'])->name('qcrejection.store');
    Route::get('production_rejection/create', [StagewiseReceiveController::class,'productionRejection'])->name('productionrejection');
    Route::get('production_rejection/list', [StagewiseReceiveController::class,'productionRejectionList'])->name('productionrejectionlist');
    Route::post('production_qrreceive/rc_rej', [StagewiseReceiveController::class,'productionQRRcRejFetchEntry'])->name('productionqrrcrejfetchdata');
    Route::post('production-qrrejection/store', [StagewiseReceiveController::class,'productionQRRcRejStore'])->name('productionrejection.store');
    Route::get('pts_fqc/create', [FinalQcInspectionController::class,'ptsFqcCreate'])->name('ptsfqccreate');
    Route::post('pts_fqc/store', [FinalQcInspectionController::class,'ptsFqcApproval'])->name('pts_fqc_approval.store');
    Route::post('/invoicedetails/index', [InvoiceDetailsController::class,'index'])->name('invoicedetails');
    Route::get('/invoicepart-data/id', [InvoiceDetailsController::class,'cusPartData'])->name('cuspartdata');
    Route::post('invoicefetch-rc', [InvoiceDetailsController::class,'invoiceItemRc'])->name('invoiceitemrc');
    Route::post('invoicefetch-rcqty', [InvoiceDetailsController::class,'invoiceQtyRc'])->name('invoiceqtyrc');
    Route::get('invoice_correction-request', [InvoiceDetailsController::class,'invoiceCorrectionRequest'])->name('invoice_correction_request');
    Route::get('invoicefetch-request', [InvoiceDetailsController::class,'invoiceFetchData'])->name('invoicefetchdata');
    Route::get('/invoice_correction-form/id', [InvoiceDetailsController::class,'invoiceCorrectionForm'])->name('invoicecorrectionform');
    Route::get('/invoice_print-form/id', [InvoiceDetailsController::class,'invoicePrint'])->name('invoiceprint');
    Route::post('invoice_print-pdf', [InvoiceDetailsController::class,'invoicePrintPdf'])->name('invoiceprintpdf');
    Route::get('/invoice_reprint-form/id', [InvoiceDetailsController::class,'invoiceRePrint'])->name('invoicereprint');
    Route::get('invoicereprintfetch-request', [InvoiceDetailsController::class,'invoiceReprintFetchDatas'])->name('invoicereprintfetchdata');
    Route::post('invoice_reprint-pdf', [InvoiceDetailsController::class,'invoiceRePrintPdf'])->name('invoicereprintpdf');
    Route::get('/supplymentaryinvoice/list', [InvoiceDetailsController::class,'supplymentaryInvoice'])->name('supplymentaryinvoice');
    Route::get('supplymentaryinvoice_receive/create', [InvoiceDetailsController::class,'supplymentaryInvoiceCreateForm'])->name('supplymentaryinvoice.create');
    Route::post('supplymentaryinvoice_receive/store', [InvoiceDetailsController::class,'supplymentaryInvoiceStore'])->name('supplymentaryinvoice.store');
    Route::get('/supplymentaryinvoice_print-form/id', [InvoiceDetailsController::class,'supplymentaryInvoicePrint'])->name('supplymentaryinvoiceprint');
    Route::get('/supplymentaryinvoice_reprint-form/id', [InvoiceDetailsController::class,'supplymentaryReInvoicePrint'])->name('supplymentaryreinvoiceprint');
    Route::post('supplymentaryinvoicefetch-po', [InvoiceDetailsController::class,'supplymentaryinvoiceItemPo'])->name('supplymentaryinvoiceitempo');
    Route::get('/traceability-form/id', [InvoiceDetailsController::class,'traceability'])->name('traceability');
    Route::post('rccheckdata', [InvoiceDetailsController::class,'rcCheckData'])->name('rcinvoice_data');
    Route::get('/user-management/id', [UserController::class,'userIndex'])->name('userindex');
    Route::get('/user-management/create', [UserController::class,'userCreate'])->name('usercreate');
    Route::post('/user-management/store', [UserController::class,'userStore'])->name('userstore');
    Route::get('user-management/{id}/edit', [UserController::class,'userEdit'])->name('useredit');
    Route::get('department/export/excel', [DepartmentController::class, 'export_excel'])->name('department.export_excel');
    Route::post('rmreturn-receive/part_no', [RetrunRMDetailsController::class,'rmReturnPartFetchEntry'])->name('rmreturnpartfetchdata');
    Route::post('/sales_plancustomerpart-fetchdata', [SalesDespatchPlanSummaryController::class,'planCusPartFetchData'])->name('plancuspartfetchData');
    Route::post('/sales_planpartcover-fetchdata', [SalesDespatchPlanSummaryController::class,'planPartCoverfetchData'])->name('planpartcoverfetchData');
    Route::get('sales_plancustomer_fg/list', [SalesDespatchPlanSummaryController::class,'salesPlanFGList'])->name('salesplanfglist');
    Route::post('sales_plancustomer_fg/store', [SalesDespatchPlanSummaryController::class,'salesPlanFGStore'])->name('salesplanfgstore');
    Route::get('sales_plan_confirm/list', [SalesDespatchPlanSummaryController::class,'salesPlanConfirm'])->name('salesplanconfirm');
    Route::post('sales_invoiceconfirmation/store', [SalesDespatchPlanSummaryController::class,'salesConfirmationEntry'])->name('salesconfirmationentry');
    Route::post('partrmrequistionfetchdata', [RmRequistionController::class,'partRmRequistionFetchData'])->name('partrmrequistionfetchdata');
    Route::post('rmrequistioncheckdata', [RmRequistionController::class,'rmRequistionCheckData'])->name('rmrequistioncheckdata');
    Route::post('rmrequistionfetchdata', [RmRequistionController::class,'rmRequistionFetchData'])->name('rmrequistionfetchdata');
    Route::post('rmrequistionfetchdata-2', [RmRequistionController::class,'rmRequistionFetchData2'])->name('rmrequistionfetchdata2');
    Route::post('/grnqc-rm_issuefetchdata', [RmRequistionController::class,'grnQcRmIssueFetchData'])->name('grnqcrmissuefetchdata');
    Route::post('/grnqc-rm_issuefetchdata2', [RmRequistionController::class,'grnQcRmIssueFetchData2'])->name('grnqcrmissuefetchdata2');
    Route::post('rmrequistionstore', [RmRequistionController::class,'rmRequistionStore'])->name('rmrequistionstore');
    // Route::POST('invoice-report/export', [InvoiceDetailsController::class, 'export'])->name('invoice_report_export');
    Route::get('invoice-report/export', [InvoiceDetailsController::class, 'export'])->name('invoice_report_export');
    Route::get('/invoicereport-partdata/id', [InvoiceDetailsController::class,'invoiceCustomerPartData'])->name('invoicecustomerpartdata');
    Route::get('/invoicereport-Customerdata/id', [InvoiceDetailsController::class,'invoicePartCustomerData'])->name('invoicepartcustomerdata');
    Route::get('rawmaterial-report/export', [RawMaterialController::class, 'export'])->name('rawmaterial_export');
    Route::get('rawmaterialcatergory-data', [RawMaterialController::class, 'rawCategoryFetchData'])->name('rawcategoryfetchdata');
    Route::get('rawmaterial-data', [RawMaterialController::class, 'rawFetchData'])->name('rawfetchdata');
    Route::get('rawmaterialcatrgory-report/export', [RawMaterialCategoryController::class, 'export'])->name('raw_material_category_export');
    Route::get('suppliers-report/export', [SupplierController::class, 'export'])->name('supplier_export');
    Route::get('invoice/sticker', [SalesDespatchPlanSummaryController::class,'invoiceStickerCreate'])->name('invoicestickercreate');
    Route::get('invoice/sticker-fetch', [SalesDespatchPlanSummaryController::class,'invoiceStickerFetch'])->name('invoicestickerfetch');
    Route::post('invoice_sticker-box/print', [InvoiceDetailsController::class,'invoiceStickerPrint'])->name('invoicestickerprint');
    Route::get('invoice_sticker-boschbox/print', [InvoiceDetailsController::class,'invoiceBoschStickerPrint'])->name('invoiceboschstickerprint');
    Route::get('productioncoverwiseentry/create', [StagewiseReceiveController::class,'productionCoverwiseEntry'])->name('productioncoverwiseentry');
    Route::get('productioncoverwiseentry/list', [StagewiseReceiveController::class,'productionCoverwiseList'])->name('productioncoverwiselist');
    Route::post('productioncoverwiseentry/fetchdata', [StagewiseReceiveController::class,'productionCoverwiseFetchEntry'])->name('productioncoverwisefetchdata');
    Route::post('productioncoverwiseentry/store', [StagewiseReceiveController::class,'productionCoverwiseStore'])->name('productioncoverwise.store');
    Route::get('cncproduction/coverwise-create', [StagewiseReceiveController::class,'cncProductionCoverwiseCreate'])->name('cncproductioncoverwisecreate');
    Route::get('cncproduction/coverwise-list', [StagewiseReceiveController::class,'cncProductionCoverwiselist'])->name('cncproductioncoverwiselist');
    Route::post('cncproduction/part_id', [StagewiseReceiveController::class,'cncProductionCoverwiseRcFetchEntry'])->name('cncproductioncoverwisercfetchdata');
    Route::post('cncproduction/part_no', [StagewiseReceiveController::class,'cncProductionCoverwisePartFetchEntry'])->name('cncproductioncoverwisepartissuefetchdata');
    Route::get('cncproduction-receive/fqc_approval', [StagewiseReceiveController::class,'cncProductionFqcApproval'])->name('cncproductionfqc');
    Route::get('cncproduction-firewall/list', [StagewiseReceiveController::class,'cncProductionFirewallReceiveList'])->name('cncproductionfirewallreceive');
    Route::get('cncproduction-firewall/create', [StagewiseReceiveController::class,'cncProductionFirewallReceiveCreateForm'])->name('cncproductionfirewallreceive.create');
    Route::post('cncproduction-firewall', [StagewiseReceiveController::class,'cncProductionFirewallReceiveEntry'])->name('cncproductionfirewallreceive.store');
    Route::post('cncproduction-firewall/part_no', [StagewiseReceiveController::class,'cncProductionFirewallReceivePartFetchEntry'])->name('cncproductionfirewallpartreceivefetchdata');
    Route::post('cncproduction-firewall/rc_no', [StagewiseReceiveController::class,'cncProductionFirewallRcFetchEntry'])->name('cncproductionfirewallrcissuefetchdata');
    Route::get('pts_stock-report/export', [StagewiseReceiveController::class, 'pts_export'])->name('pts_export');
    Route::post('processpart/check', [ProductProcessMasterController::class, 'processPartCheck'])->name('processpartcheck');
    Route::post('processpartmachine/check', [ProductProcessMasterController::class, 'processPartMachineCheck'])->name('processpartmachinecheck');
    Route::get('productionfirewall/list', [StagewiseReceiveController::class,'productionfirewallList'])->name('productionfirewalllist');
    Route::get('productionfirewall/create', [StagewiseReceiveController::class,'productionfirewallEntry'])->name('productionfirewallentry');
    Route::post('productionfirewall/fetchdata', [StagewiseReceiveController::class,'productionfirewallFetchEntry'])->name('productionfirewallfetchdata');
    Route::post('productionfirewall-cover/fetchdata', [StagewiseReceiveController::class,'productionfirewallCoverFetchEntry'])->name('productionfirewallcoverfetchdata');
    Route::post('productionfirewall/store', [StagewiseReceiveController::class,'productionfirewallStore'])->name('productionfirewall.store');
    Route::post('productionfirewall/coverfetchdata', [StagewiseReceiveController::class,'partCoverwiseFetchData'])->name('partcoverwisefetchdata');
    Route::post('productionfirewall/coverqtyfetchdata', [StagewiseReceiveController::class,'partCoverwiseQtyFetchData'])->name('partcoverwiseqtyfetchdata');
    Route::post('productionfirewall/coverrcfetchdata', [StagewiseReceiveController::class,'partCoverwiseRcFetchData'])->name('partcoverwisercfetchdata');
    Route::get('ptsreworkrevert/list', [StagewiseReceiveController::class,'ptsReworkRevertList'])->name('ptsreworkrevertlist');
    Route::get('ptsreworkrevert/create', [StagewiseReceiveController::class,'ptsReworkRevertCreate'])->name('ptsreworkrevertcreate');
    Route::post('ptsrcrework/revertfetchdata', [StagewiseReceiveController::class,'ptsRcreworkrevertfetchdata'])->name('ptsrcreworkrevertfetchdata');
    Route::post('ptspartrework/revertfetchdata', [StagewiseReceiveController::class,'ptsReworkrevertpartreceivefetchdata'])->name('ptsreworkrevertpartreceivefetchdata');
    Route::post('ptspartrework/stickerqtyfetchdata', [StagewiseReceiveController::class,'ptsreworkstickerfetchdata'])->name('ptsreworkstickerfetchdata');
    Route::post('ptspartrework/store', [StagewiseReceiveController::class,'ptsreworkrevertStoredata'])->name('ptsreworkrevert.store');
    Route::get('/reports/dc-report', [DCReportController::class, 'index'])->name('dc-report.index');
    Route::get('/reports/dc-report/export', [DCReportController::class, 'export'])->name('dc-report.export');
    Route::get('open-route-card-report', [TransDataD12Controller::class, 'openRouteCardReport'])->name('open_route_card.report');
    Route::get('open-route-card-report/export', [TransDataD12Controller::class, 'exportOpenRouteCardReport'])->name('open_route_card.export');

    Route::resources([
        'users' => UserController::class,
        'products' => ProductController::class,
        'department' => DepartmentController::class,
        'raw_material_category' => RawMaterialCategoryController::class,
        'raw_material' => RawMaterialController::class,
        'supplier' => SupplierController::class,
        'supplier-products' => SupplierProductController::class,
        'po' => PODetailController::class,
        'po-products' => POProductDetailController::class,
        'po-correction' => PoCorrectionController::class,
        'rack-stockmaster' => RackStockmasterController::class,
        'rackmaster' => RackmasterController::class,
        'grn_inward' => GRNInwardRegisterController::class,
        'grn_heat_no' => HeatNumberController::class,
        'grn_qc' => GrnQualityController::class,
        'grnqcrejection' => GrnRejectionController::class,
        'process-master' => ItemProcesmasterController::class,
        'productprocessmaster' => ProductProcessMasterController::class,
        'dc_master' => DcMasterController::class,
        'fqc_approval'=>FinalQcInspectionController::class,
        'customermaster'=>CustomerMasterController::class,
        'customer-products'=>CustomerProductMasterController::class,
        'customerpomaster'=>CustomerPoMasterController::class,
        'delivery_challan'=>DcTransactionDetailsController::class,
        'dcprint'=>DcPrintController::class,
        'invoicedetails'=>InvoiceDetailsController::class,
        'invoicecorrectionmaster'=>InvoiceCorrectionMasterController::class,
        'invoicecorrectiondetail'=>InvoiceCorrectionDetailController::class,
        'stageqrcodelock'=>StageQrCodeLockController::class,
        'retrunrmdetails'=>RetrunRMDetailsController::class,
        'rmdc'=>RMDcController::class,
        'rmrequistion'=>RmRequistionController::class,
        'salesdespatchplansummary'=>SalesDespatchPlanSummaryController::class,
        'salesdespatchplantransaction'=>SalesDespatchPlanTransactionController::class,
    ]);
});

