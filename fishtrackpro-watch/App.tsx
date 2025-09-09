import React, { useEffect } from 'react';
import {
  StatusBar,
  Platform,
  PermissionsAndroid,
  Alert,
} from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import AppNavigator from './src/navigation/AppNavigator';
import { biometricService } from './src/services/biometricService';
import { apiService } from './src/services/api';

const App: React.FC = () => {
  useEffect(() => {
    initializeApp();
  }, []);

  const initializeApp = async () => {
    try {
      // Request necessary permissions
      await requestPermissions();
      
      // Initialize services
      await initializeServices();
      
      // Sync offline data if any
      await syncOfflineData();
    } catch (error) {
      console.error('Error initializing app:', error);
    }
  };

  const requestPermissions = async () => {
    if (Platform.OS === 'android') {
      try {
        const permissions = [
          PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
          PermissionsAndroid.PERMISSIONS.ACCESS_COARSE_LOCATION,
          PermissionsAndroid.PERMISSIONS.BODY_SENSORS,
          PermissionsAndroid.PERMISSIONS.ACTIVITY_RECOGNITION,
          PermissionsAndroid.PERMISSIONS.CAMERA,
          PermissionsAndroid.PERMISSIONS.WRITE_EXTERNAL_STORAGE,
          PermissionsAndroid.PERMISSIONS.READ_EXTERNAL_STORAGE,
        ];

        const granted = await PermissionsAndroid.requestMultiple(permissions);
        
        const allGranted = Object.values(granted).every(
          permission => permission === PermissionsAndroid.RESULTS.GRANTED
        );

        if (!allGranted) {
          Alert.alert(
            'Разрешения',
            'Некоторые разрешения не предоставлены. Функциональность приложения может быть ограничена.',
            [{ text: 'OK' }]
          );
        }
      } catch (error) {
        console.error('Error requesting permissions:', error);
      }
    }
  };

  const initializeServices = async () => {
    try {
      // Initialize biometric service
      await biometricService.requestPermissions();
      
      // Initialize API service
      // API service is already initialized in its constructor
      
      console.log('Services initialized successfully');
    } catch (error) {
      console.error('Error initializing services:', error);
    }
  };

  const syncOfflineData = async () => {
    try {
      // Check if there's offline data to sync
      const offlineData = await apiService.getOfflineData('biometric_data');
      
      if (offlineData) {
        // Attempt to sync offline data
        await apiService.syncOfflineData();
        console.log('Offline data synced successfully');
      }
    } catch (error) {
      console.error('Error syncing offline data:', error);
    }
  };

  return (
    <SafeAreaProvider>
      <StatusBar
        barStyle="light-content"
        backgroundColor="#4CAF50"
        translucent={false}
      />
      <AppNavigator />
    </SafeAreaProvider>
  );
};

export default App;
