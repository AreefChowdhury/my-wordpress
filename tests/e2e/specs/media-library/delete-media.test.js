/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import path from 'path';

test.describe( 'Delete Media', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		test.setTimeout(180000); 
        const file0 = './tests/e2e/assets/test_data_image1.jpeg';
        await requestUtils.uploadMedia(file0);
        const file1 = './tests/e2e/assets/test_data_image2.jpeg';
        await requestUtils.uploadMedia(file1);
        const file2 = './tests/e2e/assets/test_data_image3.jpeg';
        await requestUtils.uploadMedia(file2);
	} );

	test.afterAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllMedia();
	} );

	test( 'delete single media', async ( { page, admin } ) => {
		await admin.visitAdminPage( 'upload.php?mode=list' );

		// Hover on the first media.
		await page
			.locator(
				'tr td.title.column-title.has-row-actions.column-primary'
			)
			.first()
			.hover();
		page.once( 'dialog', ( dialog ) => {
			dialog
				.accept()
				.catch( ( err ) =>
					console.error( 'Dialog accept failed:', err )
				);
		} );
		await page
			.locator( "tr[id^='post-'] a[aria-label^='Delete']" )
			.first()
			.click();

		await expect(
			page.locator( '#message p' ),
			'Media got deleted successfully'
		).toBeVisible();
	} );

	test( 'delete Bulk media', async ( { page, admin } ) => {
		await admin.visitAdminPage( 'upload.php?mode=list' );

		// Select the multiple media from the list.
		await page.locator( 'input[name="media[]"]' ).first().click();
		await page.locator( 'input[name="media[]"]' ).nth( 1 ).click();

		await page
			.locator( '#bulk-action-selector-top' )
			.selectOption( 'delete' );

		page.once( 'dialog', ( dialog ) => {
			dialog
				.accept()
				.catch( ( err ) =>
					console.error( 'Dialog accept failed:', err )
				);
		} );

		await page.getByRole( 'button', { name: 'Apply' } ).first().click();

		await expect(
			page.locator( '#message p' ),
			'Media got deleted successfully'
		).toBeVisible();
	} );
} );
