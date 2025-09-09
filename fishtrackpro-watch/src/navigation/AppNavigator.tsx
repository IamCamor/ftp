import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { RootStackParamList } from '../types';

// Screens
import HomeScreen from '../screens/HomeScreen';
import StartSessionScreen from '../screens/StartSessionScreen';
import ActiveSessionScreen from '../screens/ActiveSessionScreen';
import RecordCatchScreen from '../screens/RecordCatchScreen';
import SessionCompleteScreen from '../screens/SessionCompleteScreen';
import HistoryScreen from '../screens/HistoryScreen';
import SettingsScreen from '../screens/SettingsScreen';
import CoachScreen from '../screens/CoachScreen';
import AnalyticsScreen from '../screens/AnalyticsScreen';
import ProfileScreen from '../screens/ProfileScreen';

const Stack = createStackNavigator<RootStackParamList>();

const AppNavigator: React.FC = () => {
  return (
    <NavigationContainer>
      <Stack.Navigator
        initialRouteName="Home"
        screenOptions={{
          headerShown: false,
          gestureEnabled: true,
          cardStyleInterpolator: ({ current, layouts }) => {
            return {
              cardStyle: {
                transform: [
                  {
                    translateX: current.progress.interpolate({
                      inputRange: [0, 1],
                      outputRange: [layouts.screen.width, 0],
                    }),
                  },
                ],
              },
            };
          },
        }}
      >
        <Stack.Screen
          name="Home"
          component={HomeScreen}
          options={{
            title: 'FishTrackPro',
          }}
        />
        
        <Stack.Screen
          name="StartSession"
          component={StartSessionScreen}
          options={{
            title: 'Начать рыбалку',
            gestureEnabled: true,
          }}
        />
        
        <Stack.Screen
          name="ActiveSession"
          component={ActiveSessionScreen}
          options={{
            title: 'Активная сессия',
            gestureEnabled: false, // Prevent accidental navigation during session
          }}
        />
        
        <Stack.Screen
          name="RecordCatch"
          component={RecordCatchScreen}
          options={{
            title: 'Записать улов',
            gestureEnabled: true,
          }}
        />
        
        <Stack.Screen
          name="SessionComplete"
          component={SessionCompleteScreen}
          options={{
            title: 'Сессия завершена',
            gestureEnabled: false, // Force user to acknowledge completion
          }}
        />
        
        <Stack.Screen
          name="History"
          component={HistoryScreen}
          options={{
            title: 'История сессий',
            gestureEnabled: true,
          }}
        />
        
        <Stack.Screen
          name="Settings"
          component={SettingsScreen}
          options={{
            title: 'Настройки',
            gestureEnabled: true,
          }}
        />
        
        <Stack.Screen
          name="Coach"
          component={CoachScreen}
          options={{
            title: 'Тренер',
            gestureEnabled: true,
          }}
        />
        
        <Stack.Screen
          name="Analytics"
          component={AnalyticsScreen}
          options={{
            title: 'Аналитика',
            gestureEnabled: true,
          }}
        />
        
        <Stack.Screen
          name="Profile"
          component={ProfileScreen}
          options={{
            title: 'Профиль',
            gestureEnabled: true,
          }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
};

export default AppNavigator;
