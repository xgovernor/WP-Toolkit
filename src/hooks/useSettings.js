import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from 'react';

/**
 * Function for saving settings
 *
 * @param {Object} values The settings to save as key-value pairs
 * @return {Promise} A promise that resolves to an object with an update key
 */
const saveSettings = async ( values ) => {
	const r = await apiFetch( {
		path: '/pm2-modern-plugin/v1/settings',
		method: 'POST',
		data: values,
	} ).then( ( res ) => {
		return res;
	} );
	return { update: r };
};

/**
 * Function for getting all settings
 *
 * @return {Promise} A promise that resolves to an object with all settings
 */
const getSettings = async () => {
	const r = await apiFetch( {
		path: '/pm2-modern-plugin/v1/settings',
		method: 'GET',
	} ).then( ( res ) => {
		return res;
	} );
	return r;
};

/**
 * Hook for using settings
 *
 * @return {Object} {saveSettings: function,getSettings:function, isLoaded: boolean, isSaving: boolean, hasSaved: boolean}
 */
export const useSettings = () => {
	const [ isSaving, setIsSaving ] = useState( false );
	const [ hasSaved, setHasSaved ] = useState( false );
	const [ isLoaded, setIsLoaded ] = useState( false );
	//Reset the isSaving state after 2 seconds
	useEffect( () => {
		if ( hasSaved ) {
			const timer = setTimeout( () => {
				setIsSaving( false );
			}, 2000 );
			return () => clearTimeout( timer );
		}
	}, [ hasSaved ] );
	return {
		saveSettings: ( values ) => {
			setIsSaving( true );
			saveSettings( values ).then( () => {
				setIsSaving( false );
				setHasSaved( true );
			} );
		},
		getSettings: () => {
			setIsLoaded( true );
			getSettings().then( () => {
				setIsLoaded( false );
			} );
		},
		isLoaded,
		isSaving,
		hasSaved,
	};
};
export default useSettings;
