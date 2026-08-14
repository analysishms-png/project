# Channel Module

## Overview

The Channel module handles online channel management, rate push, and OTA (Online Travel Agency) integrations.

---

## Components

### Controllers
- `ChannelPush` - Channel push operations
- `ChannelPublic` - Public channel operations

### Models
- `ChannelDerived` - Channel derived rates
- `ChannelPushes` - Channel pushes
- `ChannelEnviro` - Channel environment
- `ChannelRate` - Channel rates

### Services
- None (uses controllers directly)

---

## Workflows

### Rate Push Flow
1. Select channel
2. Select dates
3. Set rates
4. Push to channel
5. Verify push status

### Channel Sync Flow
1. Connect to channel
2. Fetch availability
3. Sync rates
4. Update inventory
5. Confirm sync

---

## Database Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `channelderived` | Derived rates | channel_id, rate |
| `channelpushes` | Channel pushes | channel_id, status |
| `channelenviro` | Channel config | channel_id, config |
| `channelrate` | Channel rates | channel_id, room_cat |

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/channel` | ChannelPush@index | channel.index |
| POST | `/channel/push` | ChannelPush@push | channel.push |
| GET | `/channel/status` | ChannelPush@status | channel.status |
| GET | `/channel/public` | ChannelPublic@index | channel.public |

---

## Supported Channels

| Channel | Status | Features |
|---------|--------|----------|
| Booking.com | Active | Rates, Availability |
| Expedia | Active | Rates, Availability |
| MakeMyTrip | Active | Rates, Availability |
| Goibibo | Active | Rates, Availability |

---

## Key Features

1. **Rate Management** - Manage channel rates
2. **Availability Push** - Push availability to channels
3. **Rate Push** - Push rates to channels
4. **Booking Sync** - Sync bookings from channels
5. **Channel Reports** - View channel performance
6. **Multi-Property** - Manage multiple properties

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
