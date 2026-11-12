package com.securefin.kidswallet.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface LoginApprovalRepository extends JpaRepository<LoginApproval, Long> {

    List<LoginApproval> findByParentIdAndStatus(Long parentId, String status);

    Optional<LoginApproval> findByApprovalToken(String approvalToken);
}
