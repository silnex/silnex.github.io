---
layout: post
id: 28
title: "MySQL 테이블 저장 디렉토리 변경"
subtitle: "MySQL TableSpace 삽질"
description: "테이블의 저장 위치를 바꾸는 삽질을 기록한 내용입니다."
type: "MYSQL"
created_at: "2023-02-11"
updated_at: "2023-02-11"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/mysql-tablespace.png"
order: 28
tags: ['mysql', 'database']
comments: true
---

## 문제 발생

최근 서비스의 Database 용량이 차오르면서 저장 공간을 늘려야 하는 이슈를 맞닥드렸습니다.  

하지만 지금 사용중인 서비스에선 Disk 만 늘릴 수 있는 기능을 제공하지 않고 있어 서버의 저장 공간을 늘리면,  
사용하지도 않을 CPU/RAM 이 늘어나는 비효율적인 ~~(제가 못참는)~~ 비용이 발생하다보니, 다른 방안이 필요한 상황입니다.  

## 고민

이 부분을 어떻게 해결할지 고민 하던 중 Table의 Data Directory를 수정할 수 있는 방법이 있었으나, MySQL8에선 Alter Table 이 지원되지 않는다고 합니다. [[문서]](https://dev.mysql.com/doc/refman/8.0/en/alter-table.html#alter-table-options)

MySQL에 TableSpace 를 지정할 수 있는 부분을 찾았고,  
기존의 용량이 큰 테이블만, Block Storage 를 통해 관리하면, 사용하는 용량 만큼 데이터를 제공할 수 있다는 생각에 실행으로 옮겼습니다.

## 계획

> 1. Block Storage 를 DB 인스턴스에 붙인다.
> 2. /mnt/data 에 Block Storage 를 마운트 한다.
> 3. MySQL에서 용량이 큰 테이블의 저장 위치를 /mnt/data 로 옮긴다.

계획은 간단했으나, ~~(말은 쉽지)~~ 막상 준비하면서 여러 어려움이 있었습니다.  
특히 apparmor 때문에 고생했습니다.

## 사전 작업

### innodb_file_per_table 활성화

먼저 각 테이블별로 파일로 분리되어 있지 않으면, 모든 데이터를 옮겨야하기에 테이블을 각 파일별로 분리할 필요가 있습니다.  

다행히 제가 사용중인 `MySQL 8.0` 은 기본적으로 `innodb_file_per_table` 설정이 활성화 되어있습니다.

```SQL
SELECT @@innodb_file_per_table; -- result: 1
```

### Mount Block storage

#### 파티션 생성

1. `parted` 명령어를 통해 파티션을 생성합니다.

```bash
$ parted -s /dev/vdb mklabel gpt
$ parted -s /dev/vdb unit mib mkpart primary 0% 100%
```

2. 생성된 파티션을 ext4로 포맷합니다.  
다른 파일 시스템도 상관은 없으나, 해당 파일 시스템을 MySQL이 지원하는지 확인 해야합니다.

```bash
$ mkfs.ext4 /dev/vdb1
```

#### 파티션 마운트

1. 마운트할 위치를 만듭니다.

```bash
$ mkdir /mnt/data
```

2. `/etc/fstab` 에 기록해 부팅시 자동으로 마운트 되게 합니다.

```bash
$ vi /etc/fstab
```

```conf
# Mount Block Storage
/dev/vdb1 /mnt/data ext4 defaults,noatime,nofail 0 0
```

3. `mount /mnt/data` 를 이용해 마운트 합니다.

이렇게 Block Storage 준비는 모두 완료 되었습니다.

### MySQL 설정 변경

#### `innodb_directories` 설정

MySQL 8에선 `innodb_directories` 에 등록되지 않은 디렉토리는 테이블 스페이스로  사용할 수 없습니다. 이를 위해 등록을 해줘야하고, MySQL 또한 재시작이 필요합니다.

```bash
vi /etc/mysql/mysql.conf.d/mysqld.conf
```

```conf
innodb_directories='/mnt/data'
```

`innodb_directories` 설정은 `;` 을 통해 여러 경로를 지정할 수 있습니다.  
예를들어 `/mnt/data;/data` 이렇게 등록하면 `/mnt/data` 와 `/data` 를 모두 테이블 스페이스로 사용할 수 있습니다.

`innodb_directories` 옵션에 자세한 사항은 [MySQL 문서](https://dev.mysql.com/doc/refman/8.0/en/innodb-parameters.html#sysvar_innodb_directories)를 확인해주세요!


#### 디렉토리 권한 수정

이번에 마운트한 Block Storage인 `/mnt/data` 에 MySQL 이 읽고 쓸수 있는 권한이 필요합니다.

```bash
$ chown mysql:mysql -R /mnt/data
```
---
#### ~~비상!~~

분명히 모든 설정을 해줫는데도 mysql 데몬이 데이터를 못쓰는 경우가 발생합니다.  
심지어 `sudo -u mysql touch /mnt/data/test.ibd` 로 파일 생성을 하면, mysql의 권한으로 파일이 생성됩니다.

권한 문제도 아니고.. error.log 를 보면 권한 오류만이 표시되어있습니다.

#### 해결 방법
~~8 hours later..~~

만일 `seliunx` 를 사용하는 경우 `/var/log/audit.log` 를 확인하면됩니다.  
하지만 제가 사용하는 서버는 `ubuntu`이기에 기본적으로 `seliunx` 를 사용하지 않습니다.

그럼에도 이런 에러가 발생하는 이유는 [`apparmor`](https://ko.wikipedia.org/wiki/AppArmor) 때문입니다.

기본적으로 (제가 설치한 이미지에는) `apparmor`가 설치 되어있었고, 유저/그룹 리눅스 권한이 아닌 프로세스(`mysqld`)의 권한을 제어하고 있던 거였습니다. 

#### Apparmor MySql 데몬 권한 수정

```bash
vi /etc/apparmor.d/usr.sbin.mysqld
```

아래 내용을 추가
```conf
/mnt/data/ r,
/mnt/data/** rwk,
```

apparmor 설정 로드
```bash
$ apparmor_parser -r /etc/apparmor.d/usr.sbin.mysqld
```

#### 테이블 스페이스 생성

```sql
CREATE TABLESPACE new_table DATA DIRECTORY = '/mnt/data'
```

#### 테이블 위치 이동

```sql
ALTER TABLE old_table MOVE TABLESPACE new_table;
```

# 삽질 후기

이렇게 기존의 테이블을 다른 저장소(위치)로 옮기는 방법에 대해서 알아봤습니다.

실제론 mount 한 볼륨의 iops 너무 느려 이렇게 삽질을 하고 그냥 더 큰 볼륨을 가지고 있는 걸로 변경했습니다.

그리고... 10TB를 넣었지만, 2TB 밖에 인식하는 문제로 또 이틀을 삽질하게되는데....   
이 이야기는 다음번에...
